@extends('store.layout')

@section('title', __('Review & Checkout'))

@section('content')
    <main class="bg-gray-50">
        
    </main>
@endsection
@push('scripts')
    {{--This api can't be hosted on localhost--}}
    <script type="text/javascript" src="https://sdk.monnify.com/plugin/monnify.js"></script>
    <script>
        MonnifySDK.initialize({
                amount: "{{ $order_total }}",
                currency: "{{ $order_currency }}",
                reference: "{{ $order_id }}",
                customerFullName: "{{ $billing_information['full_name'] }}",
                customerEmail: "{{ $billing_information['email'] }}",
                customerPhone: "{{ $billing_information['phone'] }}",
                apiKey: "{{ $razorpay_key }}",
                contractCode: "4294676748",
                paymentDescription: "YUM Test",
                isTestMode: true,
                onLoadStart: () => {
                    console.log("loading has started");
                },
                onLoadComplete: () => {
                    console.log("SDK is UP");
                },
                onComplete: "{{ route('razorpay_callback') }}",
                onClose: "{{ route('payment_cancelled') }}"
            });
        
    </script>
@endpush