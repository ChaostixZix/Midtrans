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
        $notif = new Veritrans_Notification();
        var_dump($request->all());
        var_dump($notif->payment_type);
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
        DB::transaction(function() use($notif) {
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
                $donation->setSuccess();
            } elseif($transaction == 'pending'){
                $donation->setPending();
            } elseif ($transaction == 'deny') {
                $donation->setFailed();
            } elseif ($transaction == 'expire') {
                $donation->setExpired();
            } elseif ($transaction == 'cancel') {
                $donation->setFailed();
            }

        });

        return;
    }
}
