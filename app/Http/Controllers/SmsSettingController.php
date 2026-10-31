<?php

namespace App\Http\Controllers;

use App\Customer;
use App\SmsApi;
use App\SmsGroup;
use App\SmsSetting;
use App\Supplier;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SmsSettingController extends Controller
{


    public function index()
    {

        $apis = SmsSetting::with('company:name,id')->get();
        return view('admin.sms-api.settings.index', compact('apis'));
    }
    public function sendSms()
    {
        $data['customers'] = Customer::customers()->paginate(100);
        $data['suppliers'] = Supplier::suppliers()->paginate(100);
        $data['smsGroups'] = SmsGroup::paginate(100);
        return view('admin.sms-api.new-send-sms', $data);
//        return view('admin.sms-api.send-sms', $data);
    }



    public function smsNumbers(Request $request){

        $data['numbers']  = $request->numbers;

        return view('admin.sms-api.new-sms-number', $data);
    }

    public function smsSending(Request $request){
//        dd($request->all());
        $SMSAPI = SmsApi::where('status',1)->first();


        $numbers  = $request->numbers;
        $message  = $request->message;
        if(is_array($numbers)){
            $numbers = implode(',',$numbers);
        }



        \Log::info("SMS test message intercepted safely in local mode: " . $numbers);
        return redirect()->back()->with('success', 'Message successfully sent (Local Mode)');


//        try {
//
//            $client = new Client();
//            $sender_mobile_number = '88'.$numbers;
//            $sms_api_url = env('SMS_OTP_BASE_URL').'?apikey='.env('SMS_OTP_API_KEY') .'&secretkey='.env('SMS_OTP_SECRET_KEY').'&callerID='.env('SMS_OTP_CALLER_ID').'&messageContent='.$message.'&toUser='.$sender_mobile_number;
//
//            $client->request('GET',$sms_api_url);
//
//            return redirect()->route('send.sms')->withSuccess('SMS sent successfully');
//        } catch (\Throwable $th) {
//            return redirect()->back()->with('error',$th->getMessage());
//        }
    }
    private function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }
    public function allsmsSending(Request $request)
    {

        $SMSAPI = SmsApi::where('status',1)->first();
//        return $request->all();
        $numbers = $request->numbers;
        $message = $request->message;

        if (is_array($numbers)) {

            foreach ($numbers as &$number) {
                 if (!$this->startsWith($number, '880')) {
                     $number = '880' . $number;
                }
            }

            unset($number);

            foreach ($numbers as $number) {
                if(is_array($number)){
                    $number = implode(',',$number);
                }
                \Log::info("Bulk SMS intercepted safely in local mode: " . $number);
                return redirect()->route('send.sms')->with('success', 'Messages successfully sent (Local Mode)');

            }

        } else {
             return redirect()->back()->with('error', 'Invalid numbers provided');
        }
    }













    public function store(Request $request){
        // dd($request);
        $request->validate([
            'name'          => 'required',
            'username'      => 'required',
            'password'      => 'required',
            'sender_number' => 'required',
            'url'           => 'required',
        ]);
        $data = array_merge($request->except('_token'), ['company_id' => companyId()]);
        try {
            SmsSetting::create($data);
            return redirect()->back()->with(['message', 'Api create success']);
        } catch (\Exception $ex) {
//            return $ex->getMessage();
            return redirect()->back()->with(['error' => 'Something error, please check']);
        }
    }
    public function show($id)
    {
        try {
            SmsSetting::where('status', 1)->update([
                'status' => 0
            ]);
            SmsSetting::find($id)->update([
                'status' => 1
            ]);
            return redirect()->back()->with('message', 'Api activated successfully');
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }

    public function edit($id)
    {
        //
    }
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            if (!$request->filled('password')) {
                $data = $request->except('password');
            }
            SmsSetting::find($id)->update($data);
            return redirect()->back()->with('message', 'Api updated successfully');
        } catch (\Exception $ex) {
//            return $ex->getMessage();
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }
    public function destroy($id)
    {
        try {
            SmsSetting::destroy($id);
        } catch (\Exception $e){
            return redirect()->back()->with('error_message', 'This api use in another table you can not to delete it');
        }
        return redirect()->back()->with(['message' => 'Module Deleted Successfully!']);
    }
    public function getActiveSmsApi(Request $request)
    {
        $currentActiveApi = SmsSetting::active()->first();
        return response()->json($currentActiveApi);
    }
}
