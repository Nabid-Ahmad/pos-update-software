<?php

namespace App\Http\Controllers;

use App\Company;
use App\FundTransfer;
use App\Traits\FileSaver;
use Illuminate\Http\Request;
use App\Package;
use App\CompanyPackage;
use Carbon\Carbon;
use Auth;

class CompanyController extends Controller
{
    use FileSaver;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role_id = auth()->user()->user_role->role_id ?? null;
        if (in_array($role_id, [1, 2]) || auth()->id() == 1 || auth()->user()->email == 'superadmin@gmail.com') {
            $companies = Company::paginate(30);
        } else {
            $companies = Company::where('id', companyId())->paginate(30);
        }
        return view('admin.global_config.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $packages = Package::orderBy('id', 'asc')->pluck('name', 'id');
        return view('admin.global_config.companies.create', compact('packages'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:160|unique:companies',
            'phone' => 'required|unique:companies',
            'email' => 'required|unique:companies|max:160',
            'company_logo' => 'required'
        ]);

        $company = new Company;
        $company->name = $request->name;
        $company->phone = $request->phone;
        $company->email = $request->email;
        $company->website = $request->website;
        $company->address = $request->address;
        $company->account_linked = $request->account_linked ?? 0;
        $company->company_logo = 'logo.jpg';
        $company->save();

        if ($request->hasFile('company_logo')) {
            $this->upload_file($request->file('company_logo'), $company, 'company_logo', 'company');
        }

        if($company){
            return \redirect('companies')->with(['message'=>'Company Created Successfully!']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::findOrFail($id);
        return view('admin.global_config.companies.show')->with('company',$company);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::find($id);
        $packages = Package::orderBy('id', 'asc')->pluck('name', 'id');
        $package_id = CompanyPackage::where(['fk_company_id' => $id])->first();
        return view('admin.global_config.companies.edit', compact('company', 'package_id', 'packages'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $this->validate($request, [
            'name' => 'required|max:160',
            'phone' => 'required|unique:companies,id,'.$request->id,
            'email' => 'required|unique:companies,id,'.$request->id.'|max:160',
        ]);

        $company->update([
                        'name'              => $request->name,
                        'phone'             => $request->phone,
                        'email'             => $request->email,
                        'website'           => $request->website,
                        'address'           => $request->address,
                        'account_linked'    => $request->account_linked,
                        'invoice_footer'    => $request->invoice_footer
                    ]);
                    
        if ($request->has('remove_logo') && $request->remove_logo == 1) {
            if ($company->company_logo && file_exists(public_path($company->company_logo))) {
                @unlink(public_path($company->company_logo));
            } elseif ($company->company_logo && file_exists($company->company_logo)) {
                @unlink($company->company_logo);
            }
            $company->update(['company_logo' => null]);
        }

        $this->upload_file($request->company_logo, $company, 'company_logo', 'company');

        return \redirect('companies')->with(['message'=>'Company Updated Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            \DB::table('company_packages')->where('fk_company_id', $id)->delete();
            \DB::table('sms_quotas')->where('fk_company_id', $id)->delete();
            \DB::table('user_roles')->where('fk_company_id', $id)->delete();
            \DB::table('software_settings')->where('fk_company_id', $id)->delete();
            \App\User::where('fk_company_id', $id)->delete();

            $company->delete();

            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->back()->with('error_message', "Cannot delete company: " . $e->getMessage());
        }

        return redirect('/companies')->with(['message' => 'Company Deleted Successfully!']);
    }
}
