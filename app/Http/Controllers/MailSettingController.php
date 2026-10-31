<?php

namespace App\Http\Controllers;

use App\MailSetting;
use Illuminate\Http\Request;

class MailSettingController extends Controller
{
    public function index()
    {
        $setting = MailSetting::companies()->first();
        return view('admin.settings.mail.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|string',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        $companyId = companyId();

        MailSetting::updateOrCreate(
            ['fk_company_id' => $companyId],
            [
                'mail_mailer' => $request->mail_mailer,
                'mail_host' => $request->mail_host,
                'mail_port' => $request->mail_port,
                'mail_username' => $request->mail_username,
                'mail_password' => $request->mail_password,
                'mail_encryption' => $request->mail_encryption,
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name' => $request->mail_from_name,
            ]
        );

        return back()->withSuccess('Mail Settings Updated Successfully');
    }
}
