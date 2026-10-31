<?php

namespace App\Http\Controllers;

use App\Role;
use Illuminate\Http\Request;
use Auth;
use App\UserRole;
use App\Company;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (isSuperAdmin()) {
            $roles = Role::with('company')->orderBy('id', 'desc')->paginate(30);
        } else {
            $roles = Role::where('fk_company_id', companyId())->orderBy('id', 'desc')->paginate(30);
        }
        return view('admin.user.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_role = UserRole::where('user_id', Auth::user()->id)->first();
        $role_id = $user_role ? $user_role->role_id : null;

        if(in_array($role_id, [1, 2]) || Auth::id() == 1 || Auth::user()->email == 'superadmin@gmail.com'){
            $companies = Company::orderBy('id', 'asc')->pluck('name', 'id');
        }else{
            $companies = null;
        }
        return view('admin.user.roles.create', compact('companies'));
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
            'name' => 'required|max:160|unique:roles',
            'status' => 'required'
        ]);

        Role::create($request->except('_token'));

        return redirect()->route('roles.index')->with(['success'=>'Role Added Successfully!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Role  $Role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $Role)
    {
        return view('admin.user.roles.show', compact('Role'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Role  $Role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $Role)
    {
        return view('admin.user.roles.edit', compact('Role'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Role  $Role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $this->validate($request, [
            'name' => 'required|max:160|unique:roles,name,'.$request->id,
            'status' => 'required'
        ]);
        $role->update($request->all());
        return redirect()->route('roles.index')->with(['success'=>'Role Updated Successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Role  $Role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $Role)
    {
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \DB::table('role_permissions')->where('fk_role_id', $Role->id)->delete();
            \DB::table('user_roles')->where('role_id', $Role->id)->delete();
            $Role->delete();
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->back()->with('error_message', 'Cannot delete role: ' . $e->getMessage());
        }
        return redirect()->back()->with(['message' => 'Role Deleted Successfully!']);
    }
}
