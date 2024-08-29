@extends('store.layout')

@section('title', __('Review & Checkout'))

@section('content')
<main class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-extrabold py-8">{{ __('Review & Checkout') }}</h1>
        <div class="flex flex-col gap-4 mb-4 -mt-4">
            @include('components.alert-success')
            @include('components.alert-danger')
        </div>
        <div class="max-w-2xl pb-24 mx-auto lg:max-w-none">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-12">
                <div>
                    @include('store.checkout.partials._customer_billing_info')
                </div>
                <!-- Order summary -->
                <div class="rounded bg-white shadow-md border border-gray-100 mt-10 pb-4 lg:mt-0">
                    <div class="p-4 lg:p-6">
                        @include('store.checkout.partials._order_summary')
                        <div class="mt-4">
                            <div class="flex flex-col mt-6">
                                <button id="rzp-button" type="submit"
                                    class="mt-4 w-full bg-primary border border-transparent rounded-sm shadow-sm py-2 px-4 text-base font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-current">
                                    {{ __('Pay Now') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    {{--This api can't be hosted on localhost--}}
    <script type="text/javascript" src="https://sdk.monnify.com/plugin/monnify.js"></script>
    <script>
        document.getElementById('rzp-button').addEventListener('click', function (event) {
            event.preventDefault();
            var ref = "{{ $payment_id }}" + Math.floor((Math.random() * 1000000000) + 1);
            MonnifySDK.initialize({
                amount: "{{ $order_total }}",
                currency: "{{ $order_currency }}",
                reference: ref,
                customerFullName: "{{ $billing_information['full_name'] }}",
                customerEmail: "{{ $billing_information['email'] }}",
                customerPhone: "{{ $billing_information['phone'] }}",
                apiKey: "{{ $razorpay_key }}",
                contractCode: "4294676748",
                paymentDescription: "YUMTest Payment",
                isTestMode: true,
                onLoadStart: () => {
                    console.log("loading has started");
                },
                onLoadComplete: () => {
                    console.log("SDK is UP");
                },
                onComplete: (response) => {

                    if (response.status === "SUCCESS") {
                        window.location.href = "{{ route('razorpay_callback') }}?paymentReference="+ response.paymentReference +"&transactionReference=" + response.transactionReference;

                    } else if (response.status === "FAILED") {
                        // Redirect to the payment cancelled route with payment_id parameter
                        window.location.href = `{{ route('payment_failed') }}?payment_id={{ $payment_id }}`;
                    } else {
                        // Optionally handle other statuses or unexpected cases
                        console.error('Unexpected status:', response.status);
                    }

                },
                onClose: (response) => {
                    if (response.paymentStatus === "USER_CANCELLED") {
                        // Redirect to the payment cancelled route with payment_id parameter
                        window.location.href = `{{ route('payment_cancelled') }}?payment_id={{ $payment_id }}`;
                    }
                }
            });
        });

    </script>
@endpush