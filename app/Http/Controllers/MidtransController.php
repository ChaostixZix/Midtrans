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
        $data = json_decode($request->get('response'), true);
        $arrCancel = [
            'pending',
            'cancel',
            'deny',
            'expire'
        ];
        if(in_array($data['transaction_status'], $arrCancel))
        {
            $cancel = \Veritrans_Transaction::cancel($data['order_id']);
            return redirect(env('PREV_URL').'/cancel');
        }elseif($data['transaction_status'] === 'settlement')
        {
            return redirect(env('PREV_URL').'/manage/payment-callback/test/midtrans');
        }

//        base64_decode(Veritrans_Config::$serverKey);
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, Veritrans_Config::getBaseUrl().'/'.$data['order_id'].'/refund');
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Accept: application/json',
//            'Content-Type: application/json',
//            'Authorization: Basic '
//        ));
//        $output = curl_exec($ch);
//        curl_close($ch);
//        var_dump($output);
    }
    public function getSnap($amount, $plan)
    {
        header("Access-Control-Allow-Origin: *");
        $params = array(
            'transaction_details' => array(
                'order_id' => rand(),
                'gross_amount' => $amount,
                'plan' => $plan
            )
        );
//        $paymentUrl = Veritrans_Snap::createTransaction($params)->redirect_url;
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
}
