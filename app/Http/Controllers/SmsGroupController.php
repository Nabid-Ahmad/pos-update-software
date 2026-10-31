<?php

namespace App\Http\Controllers;

use App\SmsGroup;
use Illuminate\Http\Request;

class SmsGroupController extends Controller
{
    public function index(){
        return view('admin.smsGroup.index',[
            'smsGroups' => SmsGroup::paginate(100),
        ]);
    }
    public function create(){}
    public function store(Request $request)
    {
        $data = $request->only('group_name', 'group_mobiles');
        $data['fk_company_id'] = 1;
        $data['fk_created_by'] = auth()->user()->id;
        $data['fk_updated_by'] = auth()->user()->id;
        $data['fk_group_id']   = 1;
        $data['status']        = 1;
        SmsGroup::create($data);


        return back()->withSuccess('Group Created Successfully');
    }

    public function show(SmsGroup $smsGroup)
    {
        dd('show');
    }

    public function edit(smsGroup $smsGroup)
    {
        return view('admin.smsGroup.edit',[
            'smsGroup' => smsGroup::where('id', $smsGroup->id)->first()
        ]);
    }

    public function update(Request $request, smsGroup $smsGroup)
    {
        $data = $request->only('group_name', 'group_mobiles');
        smsGroup::findorFail($smsGroup->id)->update($data);
        return redirect()->route('smsGroup.index')->withSuccess('Updated');
    }

    public function destroy(smsGroup $smsGroup)
    {
        smsGroup::findorFail($smsGroup->id)->delete();
        return redirect()->route('smsGroup.index')->withSuccess('Deleted');
    }
}
