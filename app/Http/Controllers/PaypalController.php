<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PaypalClient;

class PaypalController extends Controller
{
    public function paypal(Request $request)
    {
        // init paypal client
        $provider = new PayPalClient;
        // get sandbox credentials to config file
        $provider->setApiCredentials(config('paypal'));
        // get token
        $paypalToken = $provider->getAccessToken();
        // create order based on request
        $response = $provider->createOrder([
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => route('success'),
                'cancel_url' => route('cancel')
            ],
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $request->price
                    ]
                ]
            ]
        ]);

        // conditional for redirecting to paypal
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    session()->put('product_name', $request->product_name);
                    session()->put('quantity', $request->quantity);
                    return redirect()->away($link['href']);
                }
            }
            return redirect()->route('cancelled')->with('error', $response['message'] ?? 'Something went wrong. Payment cancelled.');
        } else {
            return redirect()->route('cancelled')->with('error', $response['message'] ?? 'Something went wrong. Payment cancelled.');
        }
    }

    public function success(Request $request)
    {
        // init paypal client
        $provider = new PayPalClient;
        // get sandbox credentials to config file
        $provider->setApiCredentials(config('paypal'));
        // get token
        $paypalToken = $provider->getAccessToken();
        // capture payment from paypal
        $response = $provider->capturePaymentOrder($request->token);

        // conditional for paypal response and saving payment details to db
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {

            $payment = new Payment;
            $payment->payment_id = $response['id'];
            $payment->product_name = session()->get('product_name');
            $payment->quantity = session()->get('quantity');
            $payment->amount = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
            $payment->currency = $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
            $payment->customer_name = $response['payer']['name']['given_name'] . ' ' . $response['payer']['name']['surname'];
            $payment->customer_email = $response['payer']['email_address'];
            $payment->payment_status = $response['status'];
            $payment->payment_method = 'PayPal';
            $payment->save();


            return redirect()->route('completed')->with('success', 'Transaction complete.');

            unset($_SESSION['product_name']);
            unset($_SESSION['quantity']);
        } else {

            return redirect()->route('cancelled')->with('error', $response['message'] ?? 'Something went wrong. Payment cancelled.');
        }
    }

    public function cancel(Request $request)
    {
        // redirect to cancelled view/page
        return redirect()->route('cancelled')->with('error', $response['message'] ?? 'Something went wrong. Payment cancelled.');
    }
}
