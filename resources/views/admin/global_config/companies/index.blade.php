
@php 
    $role_id = auth()->user()->user_role->role_id ?? null;
    $isMultiCompany = in_array($role_id, [1, 2]) || auth()->id() == 1 || auth()->user()->email == 'superadmin@gmail.com'; 
@endphp

@extends('admin.master')
@section('title', ' - Company List')

@section('content')
    @if ($isMultiCompany)
        <main class="app-content">
            <div class="app-title">
                <div>
                    <h1><i class="fa fa-edit"></i> Manage Company</h1>
                    <p>Manage Company Form</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    @if(Session::get('message'))
                        <div class="alert alert-success">
                            {{ Session::get('message') }}
                        </div>
                    @endif

                    @if(Session::get('error_message'))
                        <div class="alert alert-danger">
                            {{ Session::get('error_message') }}
                        </div>
                    @endif

                    <div class="tile">
                        <div class="tile-body" style="height: 740px !important;">
                            @if(hasPermission('companies.create'))
                                <a href="{{ route('companies.create') }}" class="btn btn-primary" style="float: right;"><i class="fa fa-plus"></i> Add Company</a>
                            @endif

                            <h3 class="tile-title">Company List </h3>
                            <div class="tile-body table-responsive" style="min-height: 440px">
                                <table class="table table-hover table-bordered" id="sampleTable">
                                    <thead>
                                        <tr>
                                            <th>Sl.</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($companies as $key => $company)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $company->name }}</td>
                                                <td>{{ $company->phone }}</td>
                                                <td>{{ $company->email }}</td>

                                                <td class="text-center">
                                                    <div class="btn-group btn-corner">
                                                        <a class="btn btn-primary btn-sm" title="View" href="{{ route('companies.show', $company->id) }}"> <i class="fa fa-eye"></i></a>
                                                        @if(hasPermission('companies.edit'))
                                                            <a class="btn btn-info btn-sm text-light" title="Edit" href="{{ route('companies.edit', $company->id) }}"> <i class="fa fa-edit"></i> </a>
                                                        @endif

                                                        @if(hasPermission('companies.destroy') || $isMultiCompany)
                                                            <a class="btn btn-danger btn-sm text-light" title="Delete" onclick="formSubmit('{{ $company->id }}')" href="javascript:void(0)"> <i class="fa fa-trash"></i> </a>
                                                        @endif
                                                    </div>

                                                    <form action="{{ route('companies.destroy', $company->id) }}" id="deleteForm_{{ $company->id }}" method="POST">
                                                        @csrf
                                                        @method("DELETE")
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @include('admin.includes.pagination', ['data' => $companies])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    @else
        @php $company = $companies->first() @endphp

        <main class="app-content">
            <div class="app-title">
                <div>
                    <h1><i class="fa fa-info"></i> Manage Company</h1>
                    <p>Manage Company</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="tile table-responsive">
                        <div class="tile-body" style="height: 740px !important;">
                            <h3 class="tile-title">Company Information
                                @if(hasPermission('companies.edit'))
                                    <a class="btn btn-info btn-sm pull-right text-light" title="Edit" href="{{ route('companies.edit',$company->id) }}"> <i class="fa fa-edit"></i> Edit Company</a>
                                @endif
                            </h3>
                            <table class="table table-bordered">

                                <tbody>
                                <tr>
                                    <th><strong>Company Name</strong></th>
                                    <td>{{ $company->name }}</td>
                                </tr>
                                <tr>
                                    <th><strong>Company Email</strong></th>
                                    <td>{{ $company->email }}</td>
                                </tr>
                                <tr>
                                    <th><strong>Company Phone</strong></th>
                                    <td>{{ $company->phone }}</td>
                                </tr>
                                <tr>
                                    <th><strong>Address</strong></th>
                                    <td>{{ $company->address }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    @endif

@endsection

@section('footer-script')
    <script type="text/javascript">
        function formSubmit(id) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You will not be able to recover this company!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value || result.isConfirmed) {
                        document.getElementById('deleteForm_' + id).submit();
                    }
                });
            } else if (confirm("Are you sure you want to delete this company?")) {
                document.getElementById('deleteForm_' + id).submit();
            }
        }
    </script>
@endsection
