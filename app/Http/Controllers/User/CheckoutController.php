<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CheckoutProcessRequest;
use App\Models\Payment;
use App\Models\Plan;
use App\Repositories\CheckoutRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\RazorpayRepository;
use App\Repositories\MonnifyService;
use App\Settings\BankSettings;
use App\Settings\PaymentSettings;
use App\Settings\RazorpaySettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    /**
     * @var CheckoutRepository
     */
    private CheckoutRepository $repository;
    /**
     * @var PaymentRepository
     */
    private PaymentRepository $paymentRepository;
    /**
     * @var PaymentSettings
     */
    private PaymentSettings $paymentSettings;
    protected $accessToken;
    protected $monnifyService;

    public function __construct(CheckoutRepository $repository, PaymentRepository $paymentRepository, PaymentSettings $paymentSettings, MonnifyService $monnifyService)
    {
        $this->middleware(['role:guest|student|employee']);
        $this->repository = $repository;
        $this->paymentRepository = $paymentRepository;
        $this->paymentSettings = $paymentSettings;
        $this->monnifyService = $monnifyService;

    }

    /**
     * Plan checkout page
     *
     * @param Request $request
     * @param $plan
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function checkout(Request $request, $plan)
    {
        $plan = Plan::with('category:id,name')->where('code', '=', $plan)->firstOrFail();

        return view('store.checkout.index', [
            'plan' => $plan->code,
            'billing_information' => $request->user()->preferences->get('billing_information', []),
            'payment_id' => 'payment_' . Str::random(16),
            'order' => $this->repository->orderSummary($plan),
            'paymentProcessors' => $this->repository->getPaymentProcessors(),
            'countries' => isoCountries(),
            'bankSettings' => app(BankSettings::class),
        ]);
    }

    /**
     * Process checkout and redirect to review & pay page
     *
     * @param CheckoutProcessRequest $request
     * @param $plan
     * @return string
     */
    public function processCheckout(CheckoutProcessRequest $request, $plan)
    {
        if (config('qwiktest.demo_mode')) {
            return redirect()->back()->with('errorMessage', 'Demo Mode! These settings can\'t be changed.');
        }

        $plan = Plan::with('category:id,name')->where('code', '=', $plan)->firstOrFail();

        // check the user has active subscription to the current plan
        $activeSubscriptions = auth()->user()->subscriptions()
            ->where('category_id', '=', $plan->category_id)
            ->where('ends_at', '>', now()->toDateTimeString())
            ->where('status', '=', 'active')
            ->count();

        if ($activeSubscriptions > 0) {
            return redirect()->back()->with('errorMessage', 'You already had an active subscription to ' . $plan->category->name . '.');
        }

        // Check the user has pending bank payment
        if ($request->user()->hasPendingBankPayment($plan->id)) {
            return redirect()->back()->with('errorMessage', __('A pending bank payment request already exists for this plan.'));
        }

        $orderSummary = $this->repository->orderSummary($plan);

        // Update user billing information
        $request->user()->preferences = [
            'billing_information' => [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip' => $request->zip,
            ]
        ];
        $request->user()->update();

        if ($request->payment_method == 'bank') {
            return $this->handleBankPayment($request->payment_id, $plan->id, $orderSummary);
        } elseif ($request->payment_method == 'razorpay') {
            return $this->initRazorpayPayment($request->payment_id, $plan->id, $orderSummary);
        } else {
            return redirect()->back();
        }
    }

    /**
     * Initiate Razorpay Payment
     *
     * @param $paymentId
     * @param $planId
     * @param $orderSummary
     * @return string
     */
    public function initRazorpayPayment($paymentId, $planId, $orderSummary)
    {

        // Create payment record
        try {
            $payment = $this->paymentRepository->createPayment([
                'payment_id' => $paymentId,
                'currency' => $this->paymentSettings->default_currency,
                'plan_id' => $planId,
                'user_id' => request()->user()->id,
                'payment_date' => Carbon::now()->toDateTimeString(),
                'payment_processor' => 'razorpay',
                'total_amount' => $orderSummary['total'],
                'billing_information' => request()->user()->preferences->billing_information,
                'status' => 'pending',
                'order_summary' => $orderSummary
            ]);
            if (!$payment) {
                return redirect()->back()->with('successMessage', 'Something went wrong. Please try again.');
            }
            $billing_info = request()->user()->preferences->get('billing_information', []);
            $response = $this->monnifyService->initializeTransaction(
                $orderSummary['total'],
                $billing_info['full_name'],
                $billing_info['email'],
                $paymentId,
                "YUM Test payment",
                "NGN",
                app(RazorpaySettings::class)->webhook_secret,
                "https://www.yumtest.online/callbacks/monnify"
            );
                Log::info("Transaction initialized successfully.", [
                    'status' => $response->status(),
                    'response' => $response
                ]);

            return redirect()->route('payment_success');
            // return view('store.checkout.razorpay', [
            //     'order_currency' => "NGN",
            //     'order_total' => $orderSummary['total'],
            //     'razorpay_key' => app(RazorpaySettings::class)->key_id,
            //     'billing_information' => request()->user()->preferences->get('billing_information', []),
            //     'order' => $orderSummary,
            //     'payment_id' => $paymentId,
            // ]);

        } catch (\Exception $e) {
            return redirect()->route('payment_failed');
        }
    }

    public function verifyMonnifyHook(Request $request)
    {

        $payload = $request->get('payload');
        Log::channel('daily')->info('Control hit pending payment view.', $payload);
        // Verify signature
        $validatedPayload = $request->validate([
            'transactionReference' => 'required',
            'paymentReference' => 'required',
            'amountPaid' => 'required',
            'totalPayable' => 'required',
            'paidOn' => 'required',
            'paymentStatus' => 'required',
            'paymentDescription' => 'required',
            'transactionHash' => 'required',
            'currency' => 'required',
            'paymentMethod' => 'required',
        ]);

        $transHash = $validatedPayload['transactionHash'];
        $signature = $request->header('monnify-signature');

        $calculatedHash = hash_hmac('sha512', '{$validatedPayload}', app(RazorpaySettings::class)->secret_key);

        if ($calculatedHash !== $transHash) {
            return response()->json(['success' => false], 400);
        }

        return response()->json(['success' => true], 200);

    }
    /**
     * Handle Razorpay Payment
     *
     * @param Request $request
     * @param RazorpayRepository $repository
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleRazorpayPayment(Request $request)
    {
        try {
            $transactionReference = $request->query('transactionReference');
            $paymentReference = $request->query('paymentReference');
            $response = $this->monnifyService->verifyTransaction($transactionReference);

            if ($response) {
                $payment_id = substr($paymentReference, 0, 24);
                $payment = Payment::with(['plan', 'subscription'])->where('payment_id', '=', $payment_id)->first();

                if ($payment->status == 'success' || $payment->status == 'failed' || $payment->status == 'cancelled') {
                    return redirect()->back()->with('errorMessage', 'Payment already completed or cancelled.');
                }

                $payment->transaction_id = $request->get('transactionReference');
                $payment->payment_date = Carbon::now()->toDateTimeString();

                if ($response['responseBody']['paymentStatus'] == "PAID") {

                    $payment->status = 'success';
                    $subscription = $this->paymentRepository->createSubscription([
                        'payment_id' => $payment->id,
                        'plan_id' => $payment->plan_id,
                        'user_id' => $payment->user_id,
                        'category_type' => $payment->plan->category_type,
                        'category_id' => $payment->plan->category_id,
                        'duration' => $payment->plan->duration,
                        'status' => 'active'
                    ]);

                } else if ($response['responseBody']['paymentStatus'] == "PENDING") {

                    $payment->status = 'pending';

                } else if ($response['responseBody']['paymentStatus'] == "OVERPAID") {

                    $payment->status = 'success';

                } else {

                    $payment->status = 'cancelled';
                }

                $payment->update();

                return redirect()->route('payment_success');
            } else {
                return redirect()->route('payment_pending');
            }

        } catch (\Exception $e) {
            Log::channel('daily')->error($e->getMessage());
            return redirect()->route('payment_pending');
        }
    }

    /**
     * Handle Bank Payment
     *
     * @param $paymentId
     * @param $planId
     * @param $orderSummary
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleBankPayment($paymentId, $planId, $orderSummary)
    {
        try {
            $payment = $this->paymentRepository->createPayment([
                'payment_id' => $paymentId,
                'currency' => $this->paymentSettings->default_currency,
                'plan_id' => $planId,
                'user_id' => request()->user()->id,
                'payment_date' => Carbon::now()->toDateTimeString(),
                'payment_processor' => 'bank',
                'total_amount' => $orderSummary['total'],
                'billing_information' => request()->user()->preferences->billing_information,
                'status' => 'pending',
                'order_summary' => $orderSummary
            ]);
            if (!$payment) {
                return redirect()->route('payment_failed');
            }
        } catch (\Exception $e) {
            Log::channel('daily')->error($e->getMessage());
            redirect()->back()->with('errorMessage', 'Something went wrong');
        }
        return redirect()->route('payment_pending');
    }

    public function paymentSuccess()
    {
        return view('store.checkout.payment_success');
    }

    public function paymentPending()
    {
        return view('store.checkout.payment_pending');
    }

    public function paymentCancelled(Request $request)
    {
        // Retrieve the payment ID from the query parameters
        $paymentId = $request->query('payment_id');

        if ($paymentId) {
            // Find the payment record by payment ID
            $payment = Payment::where('payment_id', $paymentId)->first();

            if ($payment) {
                // Delete the payment record
                $payment->delete();
                Log::channel('daily')->info("Payment with ID {$paymentId} has been cancelled and deleted.");
            } else {
                Log::channel('daily')->warning("No payment found for cancellation with ID {$paymentId}.");
            }
        } else {
            Log::channel('daily')->warning("No payment ID provided for cancellation.");
        }

        // Return the cancellation view to the user
        return view('store.checkout.payment_cancelled');
    }


    public function paymentFailed(Request $request)
    {
        // Retrieve the payment ID from the query parameters
        $paymentId = $request->get('payment_id');

        if ($paymentId) {
            // Find the payment record by payment ID
            $payment = Payment::where('payment_id', $paymentId)->first();

            if ($payment) {
                // Delete the payment record
                $payment->delete();
                Log::channel('daily')->info("Payment with ID {$paymentId} has been cancelled and deleted.");
            } else {
                Log::channel('daily')->warning("No payment found for cancellation with ID {$paymentId}.");
            }
        } else {
            Log::channel('daily')->warning("No payment ID provided for cancellation.");
        }
        return view('store.checkout.payment_failed');
    }
}
