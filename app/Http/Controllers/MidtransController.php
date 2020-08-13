<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Veritrans_Config;
use Veritrans_Notification;
use Veritrans_Snap;
use Illuminate\Support\Facades\DB;
class MidtransController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
        Veritrans_Config::$serverKey = config('services.midtrans.serverKey');
        Veritrans_Config::$isProduction = config('services.midtrans.isProduction');
        Veritrans_Config::$isSanitized = config('services.midtrans.isSanitized');
        Veritrans_Config::$is3ds = config('services.midtrans.is3ds');
    }

    public function handler(Request $request)
    {
        var_dump($request->all());
    }
    public function getSnap($amount)
    {
        header("Access-Control-Allow-Origin: *");
        $params = array(
            'transaction_details' => array(
                'order_id' => rand(),
                'gross_amount' => $amount,
            )
        );
        $snapToken = Veritrans_Snap::getSnapToken($params);
        return $snapToken;
    }
    public function submitDonation()
    {
        $payload = [
            'transaction_details' => [
                'order_id'      => 11,
                'gross_amount'  => 10,
            ],
            'customer_details' => [
                'first_name'    => 'Bintang Putra',
                'email'         => 'Chaostix404@gmail.com',
            ],
            'item_details' => [
                [
                    'id'       => 10,
                    'price'    => 10,
                    'quantity' => 1,
                    'name'     => 'Test'
                ]
            ]
        ];
        $snapToken = Veritrans_Snap::getSnapToken($payload);
        return response()->json(['snap' => $snapToken]);
    }
    public function notificationHandler(Request $request)
    {
        $notif = new Veritrans_Notification();
        \DB::transaction(function() use($notif) {
            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;
            $donation = Donation::findOrFail($orderId);
            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if($fraud == 'challenge') {
                        $donation->setPending();
                    } else {
                        $donation->setSuccess();
                    }
                }
            } elseif ($transaction == 'settlement') {

                // TODO set payment status in merchant's database to 'Settlement'
                // $donation->addUpdate("Transaction order_id: " . $orderId ." successfully transfered using " . $type);
                $donation->setSuccess();

            } elseif($transaction == 'pending'){

                // TODO set payment status in merchant's database to 'Pending'
                // $donation->addUpdate("Waiting customer to finish transaction order_id: " . $orderId . " using " . $type);
                $donation->setPending();

            } elseif ($transaction == 'deny') {

                // TODO set payment status in merchant's database to 'Failed'
                // $donation->addUpdate("Payment using " . $type . " for transaction order_id: " . $orderId . " is Failed.");
                $donation->setFailed();

            } elseif ($transaction == 'expire') {

                // TODO set payment status in merchant's database to 'expire'
                // $donation->addUpdate("Payment using " . $type . " for transaction order_id: " . $orderId . " is expired.");
                $donation->setExpired();

            } elseif ($transaction == 'cancel') {

                // TODO set payment status in merchant's database to 'Failed'
                // $donation->addUpdate("Payment using " . $type . " for transaction order_id: " . $orderId . " is canceled.");
                $donation->setFailed();

            }

        });

        return;
    }
}
