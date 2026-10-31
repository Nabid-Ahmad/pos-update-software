@extends('admin.master')
@section('title', ' - Send Sms')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/admin/plugin/bootstrap-tagsinput/css/bootstrap-tagsinput.css') }}">

    <style>
        .bootstrap-tagsinput, .bootstrap-tagsinput input{
            width: 100%;
        }
    </style>
@endsection
@section('content')
    <main class="app-content">
        <div class="app-title">
            <div class="div">
                <h1><i class="fa fa-laptop"></i> Sms Send</h1>
                <p>Send SMS Form</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="#">Sms Send</a></li>
            </ul>
        </div>

        <!-- Navs-->
        <div class="tile mb-4">
            <div class="row">
                <div class="col-lg-12">
                    <div class="page-header">
                        <h2 class="mb-3 line-head" id="navs">Send SMS</h2>
                    </div>
                </div>
            </div>

            <div class="alert-message"></div>

            <div class="row" style="margin-bottom: 2rem;">
                <div class="col-lg-12">
                    <div class="bs-component">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#customers">Customers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#suppliers">Suppliers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#group">Custom Numbers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#groupssms">Group SMS</a>
                            </li>
                        </ul>


                        <div class="tab-content" id="myTabContent">
                            <!-- sms send for customer number -->
                            <div class="tab-pane fade active show" id="customers">
                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="tile" style="margin-top: 1px;">
                                            <h3 class="tile-title">Send SMS To Customers </h3>
                                            <div class="tile-body" style="min-height: 440px">

                                                {{-- <div class="form-group row add_asterisk">
                                                    <label class="control-label col-md-3">Message</label>
                                                    <div class="col-md-8">
                                                        <textarea name="message" style="height: 70px" class="form-control customer-message-area" rows="5" type="text" placeholder="Message">{{ old('message') }}</textarea>
                                                        <span id="chars"></span>
                                                        <div class="text-danger">
                                                            {{ $errors->has('message') ? $errors->first('message'):'' }}
                                                        </div>
                                                    </div>
                                                </div> --}}


                                                <form action="{{ route('sms-numbers') }}" method="post">
                                                    @csrf
                                                    <div class="form-group row add_asterisk">
                                                        {{-- <label class="control-label col-md-3">Customers</label> --}}
                                                        <div class="col-md-12 table-wrapper-scroll-y">
                                                            <table class="table table-hover no-spacing dataTable dataTables_wrapper" id="sampleCustomerTable">
                                                                <thead>
                                                                <tr>
                                                                    <th style="width: 13%;">
                                                                        <div class="animated-checkbox">
                                                                            <label>
                                                                                <input type="checkbox" class="customerCheckboxAll" id="customerCheckboxAll">
                                                                                <span class="label-text">Check All</span>
                                                                            </label>
                                                                        </div>
                                                                    </th>
                                                                    <th>Name</th>
                                                                    <th>Phone</th>
                                                                </tr>
                                                                </thead>
                                                                @foreach ($customers as $customer)
                                                                    <tr>
                                                                        <td>
                                                                            <div class="animated-checkbox" style="margin-left: 10px;">
                                                                                <label>
                                                                                    <input type="checkbox" class="customerCheckbox" id="cust{{ $customer->id }}" data-customer-mobile="{{ $customer->phone }}" name="numbers[]" value="{{ $customer->phone }}">
                                                                                    <span class="label-text"> </span>
                                                                                </label>
                                                                            </div>
                                                                        </td>
                                                                        <td class="align-top">
                                                                            <label for="cust{{ $customer->id }}">{{ $customer->name }}</label>
                                                                        </td>
                                                                        <td class="align-top">
                                                                            {{ $customer->phone }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach

                                                            </table>

                                                        </div>
                                                    </div>

                                                    <div class="tile-footer">
                                                        <div class="row">
                                                            <div class="col-md-7">
                                                                {{-- {{ $customers->links() }} --}}
                                                            </div>
                                                            <div class="col-md-5">
                                                                <div class="float-right">
                                                                    <button class="btn btn-primary pull-right" type="submit">
                                                                        <i class="fa fa-fw fa-lg fa-check-circle"></i> Send Customer SMS
                                                                    </button>
                                                                </div>
                                                                {{-- <div class="animated-checkbox float-right" style="margin-right: 20px">
                                                                    <label>
                                                                        <input type="checkbox" class="customerCheckboxAll" checked="checked">
                                                                        <span class="label-text"> Check All</span>
                                                                    </label>
                                                                </div> --}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- sms send for supplier number -->
                            <div class="tab-pane fade" id="suppliers">
                                <div class="tile" style="margin-top: 1px;">
                                    <h3 class="tile-title">Send SMS To Supplier </h3>
                                    <div class="tile-body" style="min-height: 440px">

                                        {{-- <div class="form-group row add_asterisk">
                                            <label class="control-label col-md-3">Message</label>
                                            <div class="col-md-8">
                                                <textarea name="message" class="form-control supplier-message-area" style="height: 70px" rows="5" type="text" placeholder="Type your message ......">{{ old('message') }}</textarea>
                                                <span id="chars"></span>
                                                <div class="text-danger">
                                                    {{ $errors->has('message') ? $errors->first('message'):'' }}
                                                </div>
                                            </div>
                                        </div> --}}


                                        <form action="{{ route('sms-numbers') }}" method="post">
                                            @csrf

                                            <div class="form-group row add_asterisk">
                                                {{-- <label class="control-label col-md-3">Suppliers</label> --}}
                                                <div class="col-md-12 table-wrapper-scroll-y">
                                                    <table class="table table-hover no-spacing dataTable" id="sampleSupplierTable">
                                                        <thead>
                                                        <tr>
                                                            <th style="width: 10%;">
                                                                <div class="animated-checkbox">
                                                                    <label>
                                                                        <input type="checkbox" class="supplierCheckboxAll" id="customerCheckboxAll">
                                                                        <span class="label-text">Check All</span>
                                                                    </label>
                                                                </div>
                                                            </th>
                                                            <th>Name</th>
                                                            <th>Phone</th>
                                                        </tr>
                                                        </thead>
                                                        @foreach ($suppliers as $supplier)
                                                            <tr>
                                                                <td>
                                                                    <div class="animated-checkbox" style="margin-left: 10px;">
                                                                        <label>
                                                                            <input type="checkbox" class="supplierCheckbox" id="supp{{ $supplier->id }}" data-supplier-mobile="{{ $supplier->phone }}" name="numbers[]" value="{{ $supplier->phone }}">
                                                                            <span class="label-text"> </span>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td class="align-top">
                                                                    <label for="supp{{ $supplier->id }}">{{ $supplier->name }}</label>

                                                                </td>
                                                                <td class="align-top">
                                                                    {{ $supplier->phone }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="tile-footer">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="float-right">
                                                            <button class="btn btn-primary pull-right send-sms-to-supplier" type="submit" type="button">
                                                                <i class="fa fa-fw fa-lg fa-check-circle"></i> Send Supplier SMS
                                                            </button>
                                                        </div>
                                                        {{-- <div class="animated-checkbox float-right" style="margin-right: 20px">
                                                            <label>
                                                                <input type="checkbox" class="supplierCheckboxAll" checked="checked">
                                                                <span class="label-text"> Check All</span>
                                                            </label>
                                                        </div> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- sms send for custom number -->
                            <div class="tab-pane fade" id="group">
                                <div class="tile" style="margin-top: 1px;">
                                    <h3 class="tile-title">Send SMS to custom numbers </h3>
                                    <div class="tile-body" style="min-height: 440px">

                                        <form class="form-horizontal" method="POST" action="{{ route('sms-sending') }}">
                                            @csrf

                                            <div class="form-group row add_asterisk">
                                                <label class="control-label col-md-3">Mobile Numbers</label>
                                                <div class="col-md-8">
                                                    <small style="color: red;display: block">use comma (,) for Seperate Numbers</small>
                                                    <input class="form-control" style="width: 100%" type="text" data-role="tagsinput" placeholder="8801xxxxxxxxx, 8801xxxxxxxxx,....." name="numbers">
                                                    {{-- <textarea name="message" style="height: 70px" class="form-control number-area" rows="5" type="text" placeholder="8801xxxxxxxxx, 8801xxxxxxxxx,.....">{{ old('message') }}</textarea> --}}
                                                    <div class="text-danger">{{ $errors->has('sent_to') ? $errors->first('sent_to'):'' }}</div>
                                                </div>
                                            </div>


                                            <div class="form-group row add_asterisk">
                                                <label class="control-label col-md-3">Message</label>
                                                <div class="col-md-8">
                                                    <textarea name="message" style="height: 70px" class="form-control custom-message-area" rows="5" type="text" placeholder="Description">{{ old('message') }}</textarea>
                                                    <div class="text-danger">
                                                        {{ $errors->has('message') ? $errors->first('message'):'' }}
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="tile-footer">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-primary pull-right send-single-message" type="submit" type="submit">
                                                            <i class="fa fa-fw fa-lg fa-check-circle"></i>Send SMS
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="groupssms">
                                <div class="tile" style="margin-top: 15px;">
                                    <h3 class="tile-title">Send SMS to custom numbers </h3>
                                    <div class="tile-body">
                                        <table class="table table-hover table-bordered">
                                            <thead>
                                            <tr>
                                                <th width="5"></th>
                                                <th width="5">SL</th>
                                                <th>Name</th>
                                                <th>Numbers Count</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($smsGroups as $key=>$smsGroup)
                                                <tr>
                                                    <td width="5">
                                                        <input type="checkbox"
                                                               class="animated-checkbox all_smsGroup_check"
                                                               style="margin-top: 5px;height: 16px;width: 16px;">
                                                    </td>
                                                    <td width="5">{{ $key+1 }}</td>
                                                    <td>{{ $smsGroup->group_name }}</td>
                                                    <td class="all_smsGroup_checkk" style="display: none">{{ $smsGroup->group_mobiles }}</td>
                                                    @php
                                                        $group_mobiles = $smsGroup->group_mobiles;
                                                        $explore_array = explode(",", $group_mobiles);
                                                        $count_mobiles = count($explore_array);
                                                    @endphp
                                                    <td>{{ $count_mobiles }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tile-body" style="min-height: 440px">
                                        <form class="form-horizontal" method="POST" action="{{ route('all-sms-sending') }}">
                                            @csrf
                                            <div class="form-group row add_asterisk">
                                                <label class="control-label col-md-3">Mobile Numbers</label>
                                                <div class="col-md-8">
                                                    <textarea name="numbers[]" style="height: 70px" class="form-control number-area" id="number_area_grp" rows="5" type="text" placeholder="8801xxxxxxxxx, 8801xxxxxxxxx,.....">{{ old('message') }}</textarea>
                                                    <div class="text-danger">{{ $errors->has('sent_to') ? $errors->first('sent_to'):'' }}</div>
                                                </div>
                                            </div>
                                            <div class="form-group row add_asterisk">
                                                <label class="control-label col-md-3">Message</label>
                                                <div class="col-md-8">
                                                    <textarea name="message" style="height: 70px" class="form-control custom-message-area2" rows="5" type="text" placeholder="Description">{{ old('message') }}</textarea>
                                                    <span>
                                                        <b class="smsCount"></b> SMS (<b class="smsLength"></b>) Characters left
                                                    </span>
                                                    <div class="text-danger">
                                                        {{ $errors->has('message') ? $errors->first('message'):'' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tile-footer">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button class="btn btn-primary pull-right send-single-message"    type="submit">
                                                            <i class="fa fa-fw fa-lg fa-check-circle"></i>Send SMS
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" class="available-balance-url" value="{{ route('get.available.sms.balance') }}">
        <input type="hidden" class="update-sms-balance-url" value="{{ route('update.sms.balance') }}">
        <input type="hidden" class="ajax-sms-route" value="{{ route('ajax.sms.send') }}">
        <input type="hidden" class="ajax-send-sms-route" value="{{ route('ajax.send.sms') }}">
    </main>
@endsection

@section('footer-script')
    <script src="{{ asset('assets/admin/plugin/bootstrap-tagsinput/js/bootstrap-tagsinput.js') }}"></script>
    <script src="{{ asset('assets/custom_js/send-custom-sms.js') }}?{{ fdate(now(), 'Y-m-d h:m') }}"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $('#sampleCustomerTable,#sampleSupplierTable').DataTable({
            // "bPaginate": false,
            "pageLength": 50,
            // "searching": false,
            // order: []
        });


        $('.all_smsGroup_check').click(function(){
            loadCheckNumbers();
        });
        function loadCheckNumbers(){
            $('#number_area_grp').val('')
            let numbers = '';
            $('.all_smsGroup_check').each(function(){
                if($(this).is(':checked')){
                    let mobile=$(this).closest('tr').find('.all_smsGroup_checkk').html()
                    numbers += mobile
                    $('#number_area_grp').val(numbers)
                }
            })
        }
    </script>

@endsection
