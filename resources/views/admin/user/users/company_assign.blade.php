@extends('admin.master')

@section('content')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-exchange"></i> Assign User to Company</h1>
                <p>Company Assign Form</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12" style="min-height: 480px">
                @include('partials._alert_message')

                <form class="form-horizontal" action="{{ route('users.company_assign.store') }}" method="POST">
                    @csrf
                    <div class="tile">
                        <h3 class="tile-title"><i class="fa fa-user-plus"></i> Assign User Company
                            <a href="{{ route('companies.index') }}" class="btn btn-primary pull-right"><i class="fa fa-list"></i> Manage Companies</a>
                        </h3>

                        <div class="tile-body" style="min-height: 200px">
                            
                            <div class="form-group row add_asterisk">
                                <label class="control-label col-md-3">User</label>
                                <div class="col-md-8">
                                    <select name="user_id" class="form-control select2" required>
                                        <option value="">Select User</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }} ({{ $user->email }}) — [Current: {{ $user->company->name ?? 'No Company' }}]
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger">{{ $errors->has('user_id') ? $errors->first('user_id'):'' }}</div>
                                </div>
                            </div>

                            <div class="form-group row add_asterisk">
                                <label class="control-label col-md-3">Company</label>
                                <div class="col-md-8">
                                    <select name="fk_company_id" class="form-control select2" required>
                                        <option value="">Select Company</option>
                                        @foreach ($companies as $id => $company)
                                            <option value="{{ $id }}">{{ $company }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-danger">{{ $errors->has('fk_company_id') ? $errors->first('fk_company_id'):'' }}</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-11">
                                    <button class="btn btn-primary pull-right" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i> Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

@section('footer-script')
    <script src="{{asset('jq/select2Loads.js')}}"></script>
@stop
