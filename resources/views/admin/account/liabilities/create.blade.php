@extends('admin.master')
@section('content')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Add Liability</h1>
            </div>
            <a href="{{ route('liabilities.index') }}" class="btn btn-primary pull-right" style="float: right;"><i class="fa fa-eye"></i> View Liability</a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">

                    <div class="tile-body" style="min-height: 740px !important;">
                        <form class="form-horizontal" method="post" action="{{ route('liabilities.store') }}">
                            @csrf

                            @include('partials._alert_message')

                            <div class="form-group row add_asterisk">
                                <label class="control-label col-md-3">Liability Name</label>
                                <div class="col-md-8">
                                    <input name="name" required class="form-control" required type="text" value="{{ old('name') }}" placeholder="Liability Name">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="control-label col-md-3">Description</label>
                                <div class="col-md-8">
                                    <textarea name="description" required class="form-control" style="min-height: 150px" cols="4" placeholder="Description of your liabilities"></textarea>
                                </div>
                            </div>

                            <div class="form-group row add_asterisk">
                                <label class="control-label col-md-3">Account (Receive Fund)</label>
                                <div class="col-md-8">
                                    <select name="account_id" class="form-control select2" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row add_asterisk">
                                <label class="control-label col-md-3">Date</label>
                                <div class="col-md-8">
                                    <input name="date" class="form-control" type="date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="form-group row add_asterisk">
                                <label class="control-label col-md-3">Amount</label>
                                <div class="col-md-8">
                                    <input name="opening" required class="form-control" onkeypress="return event.charCode >= 46 && event.charCode <= 57" required type="text" value="{{ old('opening') }}" placeholder="Your Liability Value">
                                </div>
                            </div>

                            <div class="tile-footer">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button class="btn btn-primary pull-right" type="submit">
                                            <i class="fa fa-fw fa-lg fa-check-circle"></i>Add Liability
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
