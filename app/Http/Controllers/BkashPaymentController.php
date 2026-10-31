<?php

namespace App\Http\Controllers;

use App\SoftwarePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BkashPaymentController extends Controller
{
    private $base_url;
    private $app_key;
    private $app_secret;
    private $username;
    private $password;

    public function __construct()
    {
        $this->app_key    = 'rARp9gcbyTWH2gP4yHKRPaY4tc';
        $this->app_secret = 'Bm4dA2d6PbufDdWQMMsyWarl5ABiy9khPeROx2i4w2dOkUsRZ8gd';
        $this->username   = '01844047000';
        $this->password   = '^N)hDT5R+vK';
        $this->base_url   = 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }

    public function getToken()
    {
        $request_data = array(
            'app_key' => $this->app_key,
            'app_secret' => $this->app_secret
        );
        $url = curl_init($this->base_url . '/tokenized/checkout/token/grant');
        $request_data_json = json_encode($request_data);
        $header = array(
            'Content-Type:application/json',
            'username:' . $this->username,
            'password:' . $this->password
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);
        
        $response = json_decode($resultdata, true);
        
        if (isset($response['id_token'])) {
            session()->put('bkash_token', $response['id_token']);
            return $response['id_token'];
        }
        return false;
    }

    public function createPayment(Request $request)
    {
        $token = $this->getToken();
        if (!$token) {
            return back()->with('error', 'bKash API Authentication Failed');
        }

        $order_id = $request->order_id;
        $amount = $request->amount;
        $invoice = 'BKASH-' . $order_id . '-' . time();

        $requestbody = array(
            'mode' => '0011',
            'amount' => $amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'payerReference' => $order_id,
            'merchantInvoiceNumber' => $invoice,
            'callbackURL' => route('bkash.callback') . '?order_id=' . $order_id
        );
        
        $url = curl_init($this->base_url . '/tokenized/checkout/create');
        $requestbodyJson = json_encode($requestbody);
        
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $token,
            'X-APP-Key:' . $this->app_key
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdata = curl_exec($url);
        curl_close($url);
        
        $response = json_decode($resultdata, true);
        
        if (isset($response['bkashURL'])) {
            return redirect($response['bkashURL']);
        }
        
        Log::error('bKash Create Payment Error: ' . $resultdata);
        return back()->with('error', 'Failed to create bKash payment: ' . ($response['statusMessage'] ?? 'Unknown error'));
    }

    public function callback(Request $request)
    {
        $paymentID = $request->paymentID;
        $status = $request->status;
        $order_id = $request->order_id;

        if ($status == 'success' && $paymentID) {
            $token = $this->getToken();
            
            $requestbody = array('paymentID' => $paymentID);
            $url = curl_init($this->base_url . '/tokenized/checkout/execute');
            $requestbodyJson = json_encode($requestbody);
            
            $header = array(
                'Content-Type:application/json',
                'Authorization:' . $token,
                'X-APP-Key:' . $this->app_key
            );

            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            $resultdata = curl_exec($url);
            curl_close($url);
            
            $response = json_decode($resultdata, true);
            
            if (isset($response['statusCode']) && $response['statusCode'] == '0000') {
                // Payment Success
                $paymentInfo = SoftwarePayment::find($order_id);
                if ($paymentInfo) {
                    $paymentInfo->update([
                        'status' => 1,
                        'paid_date' => date('Y-m-d'),
                        'trx_id' => $response['trxID'] ?? null,
                    ]);

                    $now = \Carbon\Carbon::now();
                    $current_end_date = \Carbon\Carbon::parse($paymentInfo->software_payment_date);

                    if ($now->greaterThan($current_end_date)) {
                        // Paid during Grace Period or Locked
                        $next_end_date = $now->copy()->addMonths($paymentInfo->duration_in_months ?? 1);
                    } else {
                        // Paid early (during Alert Period)
                        $next_end_date = $current_end_date->copy()->addMonths($paymentInfo->duration_in_months ?? 1);
                    }

                    SoftwarePayment::create([
                        'fk_company_id' => $paymentInfo->fk_company_id,
                        'fk_created_by' => $paymentInfo->fk_created_by,
                        'fk_updated_by' => $paymentInfo->fk_updated_by,
                        'fk_company_package_id' => $paymentInfo->fk_company_package_id,
                        'software_payment_date' => $next_end_date->format('Y-m-d'),
                        'amount' => $paymentInfo->amount,
                        'paid_amount' => $paymentInfo->paid_amount,
                        'alert' => $paymentInfo->alert,
                        'grace_period' => $paymentInfo->grace_period,
                        'duration_in_months' => $paymentInfo->duration_in_months ?? 1,
                        'status' => 0
                    ]);
                }
                
                return redirect()->route('home')->with('success', 'bKash Payment Successful!');
            } else {
                return redirect()->route('home')->with('error', 'bKash Execute Failed: ' . ($response['statusMessage'] ?? 'Unknown Error'));
            }
        } else if ($status == 'cancel') {
            return redirect()->route('home')->with('warning', 'bKash Payment Cancelled');
        } else {
            return redirect()->route('home')->with('error', 'bKash Payment Failed');
        }
    }
}
