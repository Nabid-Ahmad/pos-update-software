<?php

namespace App\Http\Controllers;

use App\Traits\FileSaver;
use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\UserRole;
use App\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{

    use FileSaver;

    public function __construct()
    {
        parent::__construct();
    }


    public function index(Request $request)
    {
        $users = User::with('user_role.role', 'company')
            ->companies()
            ->notAdmin()
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('email', 'LIKE', "%{$request->search}%")
                    ->orWhereHas('user_role.role', function ($qr) use($request) {
                        $qr->where('name', 'LIKE', "%{$request->search}%");
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(30);

        return view('admin.user.users.index', compact('users'));
    }

    public function create()
    {
        $user_role = UserRole::where('user_id', auth()->id())->first();
        $this_role_id = $user_role ? $user_role->role_id : null;

        if($this_role_id == 1 || $this_role_id == 2 || auth()->id() == 1 || auth()->user()->email == 'superadmin@gmail.com') {
            $companies = Company::orderBy('id', 'asc')->pluck('name', 'id');
            $roles = Role::with('company')->where('id', '>=', 2)->get();
        } else {
            $roleCompanies = optional(optional(auth()->user()->user_role)->role)->companies;
            if ($roleCompanies && $roleCompanies->isNotEmpty()) {
                $companies = $roleCompanies->pluck('name', 'id');
            } else {
                $userCompanies = auth()->user()->assignedCompanies;
                if ($userCompanies->isEmpty() && auth()->user()->fk_company_id) {
                    $companies = collect([auth()->user()->fk_company_id => auth()->user()->company->name ?? 'Company']);
                } else {
                    $companies = $userCompanies->pluck('name', 'id');
                }
            }
            $roles = Role::with('company')->where(function($q) use ($companies) {
                if ($companies && $companies->count() > 0) {
                    $q->whereIn('fk_company_id', $companies->keys())->orWhereNull('fk_company_id');
                } else {
                    $q->where('fk_company_id', companyId())->orWhereNull('fk_company_id');
                }
            })->whereNotIn('id', [1, 2])->get();
        }

        return view('admin.user.users.create', compact('roles', 'companies', 'this_role_id'));
    }

    public function store(Request $request)
    {
        // validate data
        $this->makeValidation();

        try {
            $data               = $request->except(['role_id', 'confirm_password']);
            $data['password']   = Hash::make($request->password);

            // store user to users table
            $user = User::create($data);

            // store user role to user roles table
            $this->createUserRole($request, $user);

            // <!-- update user image -->
            $this->uploadFileWithResize($request->image, $user, 'image', 'uploads/users');

            return redirect()->route('users.index')->withSuccess('User successfully created!');
        } catch (\Exception $ex) {
            return $ex->getMessage();
            return redirect()->back()->withError($ex->getMessage());
        }
    }

    public function companyAssign()
    {
        $companies = Company::orderBy('id', 'asc')->pluck('name', 'id');
        $users = User::with('assignedCompanies', 'user_role.role')
            ->companies()
            ->notAdmin()
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.user.users.company_assign', compact('companies', 'users'));
    }

    public function companyAssignStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'fk_company_id' => 'required'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $user->update(['fk_company_id' => $request->fk_company_id]);

            $userRole = UserRole::where('user_id', $user->id)->first();
            if ($userRole) {
                $userRole->update(['fk_company_id' => $request->fk_company_id]);
            }
            return redirect()->back()->withSuccess('User assigned to company successfully!');
        } catch (\Exception $ex) {
            return redirect()->back()->withError($ex->getMessage());
        }
    }

    public function companySwitch(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);

        $user = User::findOrFail(auth()->id());
        $targetCompanyId = (int)$request->company_id;

        if (!isSuperAdmin()) {
            $roleCompanies = optional(optional($user->user_role)->role)->companies;
            if ($roleCompanies && $roleCompanies->isNotEmpty()) {
                $allowedCompanyIds = $roleCompanies->pluck('id')->toArray();
            } else {
                $allowedCompanyIds = $user->assignedCompanies()->pluck('companies.id')->toArray();
                if (empty($allowedCompanyIds) && $user->fk_company_id) {
                    $allowedCompanyIds = [(int)$user->fk_company_id];
                }
            }

            if (!in_array($targetCompanyId, $allowedCompanyIds)) {
                return redirect()->back()->withError('You do not have access to this company.');
            }
        }

        \DB::table('users')->where('id', $user->id)->update(['fk_company_id' => $targetCompanyId]);
        \DB::table('user_roles')->where('user_id', $user->id)->update(['fk_company_id' => $targetCompanyId]);

        session()->put('fk_company_id', $targetCompanyId);
        session()->save();

        return redirect()->to('/home')->withSuccess('Switched company successfully!');
    }

    private function createUserRole($request, $user)
    {
        $user_role = UserRole::create([
            'fk_company_id' => $request->fk_company_id,
            'user_id'       => $user->id,
            'role_id'       => $request->role_id
        ]);
    }

    private function makeValidation()
    {
        \request()->validate([
            'name'              => 'required|unique:users,name',
            'phone'             => 'required|unique:users,phone',
            'email'             => 'required|unique:users,email',
            'role_id'           => 'required',
            'password'          => 'required|min:4',
            'confirm_password'  => 'same:password'
        ]);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('admin.user.users.show');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $authUser = Auth::user();
        $role_id = optional($authUser->user_role)->role_id;
        $isSuperAdmin = in_array($role_id, [1, 2]) || $authUser->id == 1 || $authUser->email == 'superadmin@gmail.com' || $authUser->email == 'admin@gmail.com';

        $targetIsSuperAdmin = in_array(optional($user->user_role)->role_id, [1, 2]) || $user->id == 1 || $user->email == 'superadmin@gmail.com' || $user->email == 'admin@gmail.com';
        $canEdit = $user->id == $authUser->id || $isSuperAdmin || (hasPermission('users.edit') && !$targetIsSuperAdmin);

        if($canEdit){
            if ($isSuperAdmin) {
                $roles = Role::where('id', '>=', 2)->get();
                $companies = Company::pluck('name', 'id');
            } else {
                $roleCompanies = optional(optional(auth()->user()->user_role)->role)->companies;
                if ($roleCompanies && $roleCompanies->isNotEmpty()) {
                    $companies = $roleCompanies->pluck('name', 'id');
                } else {
                    $userCompanies = auth()->user()->assignedCompanies;
                    if ($userCompanies->isEmpty() && auth()->user()->fk_company_id) {
                        $companies = collect([auth()->user()->fk_company_id => auth()->user()->company->name ?? 'Company']);
                    } else {
                        $companies = $userCompanies->pluck('name', 'id');
                    }
                }
                $roles = Role::where(function($q) use ($companies) {
                    if ($companies && $companies->count() > 0) {
                        $q->whereIn('fk_company_id', $companies->keys())->orWhereNull('fk_company_id');
                    } else {
                        $q->where('fk_company_id', companyId())->orWhereNull('fk_company_id');
                    }
                })->whereNotIn('id', [1, 2])->get();
            }
            return view('admin.user.users.edit', compact(['user', 'roles', 'companies']));
        }
        else{
            return back()->withError('You Are Not Eligible to Access This Location');
        }
    }
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name'  => 'required',
            'phone' => 'required|unique:users,phone,' . $user->id
        ]);

        try {
            // <!-- update user info -->
            $user->update($request->only(['name', 'email', 'phone', 'fk_company_id']));

            // <!-- update user image -->
            $this->uploadFileWithResize($request->image, $user, 'image', 'uploads/users');

            // <!-- update/create user role -->
            if ($request->filled('role_id')) {
                if($user->user_role) {
                    $user->user_role()->update(['role_id' => $request->role_id]);
                } else {
                    $this->createUserRole($request, $user);
                }
            }
            return redirect()->route('users.index')->withSuccess('User Updated Successfully!');
        } catch (\Exception $ex) {
            return redirect()->back()->withError($ex->getMessage());
        }
    }


    public function destroy(User $user)
    {
        try{
            DB::transaction(function () use ($user) {
                $imagePath = $user->image;
                UserRole::where('user_id', $user->id)->delete();
                $user->delete();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            });
            return redirect()->back()->withSuccess('User Deleted Successfully!');
        }catch (\Exception $ex) {
            return redirect()->back()->withError('Used in another table Can Not delete it');
        }
    }

    public function changeStatus(User $user)
    {
        $user->update(['status' => $user->status ? 0 : 1]);
        return redirect()->back()->withSuccess('Status successfully changed!');
    }

    public function changePassword(Request $request)
    {
        $request->validate(['password' => 'required|min:4']);

        try {
            User::find($request->user_id_edit)->update(['password' => \Hash::make($request->password)]);
            return redirect()->route('users.index')->withSuccess('User Password Changed Successfully!');
        } catch (\Exception $ex) {
            return redirect()->back()-withError($ex->getMessage());
        }
    }
}
