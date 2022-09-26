<?php

namespace App\Http\Controllers\Api;

use Midtrans\Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback(){
        //set konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        
        Config::$isProduction = config('services.midtrans.isProduction');
        
        Config::$isSanitized = config('services.midtrans.isSanitized');
        
        Config::$is3ds = config('services.midtrans.is3ds');

        //buat instance midtrans notification
        $notification = new Notification();
        //assign ke variable untuk memudahkan koding
        $status=$notification->transaction_status;
        $type=$notification->payment_type;
        $fraud=$notification->fraud_status;
        $order_id=$notification->order_id;
        //get transaction id
        $order=explode('-',$order_id);
        //cari transaksi bedasarkan id
        $transaction = Transaction::findOrFail($order[1]);
        //handle notif status midtrans
        if ($status == 'capture') {
            if ($type == 'credit_card'){
                if($fraud == 'challenge'){
                    $transaction->status = 'PENDING';
                }
                else {
                    $transaction->status = 'SUCCESS';
                }
            }
        }
        else if ($status == 'settlement'){
            $transaction->status = 'SUCCESS';
        }
        else if($status == 'pending'){
            $transaction->status = 'PENDING';
        }
        else if ($status == 'deny') {
            $transaction->status = 'CANCELLED';
        }
        else if ($status == 'expire') {
            $transaction->status = 'CANCELLED';
        }
        else if ($status == 'cancel') {
            $transaction->status = 'CANCELLED';
        }
        //simpan transaksi
        $transaction->save();
        //return response midtrans
        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'Midtrans Notification Success'
            ]
        ]);
    }
}
