<div class="row">
    <div class="col-md-3 pr-1">
        <div class="form-group">
            <label class="font-weight-bold">Customer [F1] <a href="#" data-toggle="modal" data-target="#addnew" title="Add New Customer"><i class="fa fa-user-plus text-primary ml-1"></i></a><span class="text-danger">*</span></label>
            <select name="fk_customer_id" class="form-control select2 selected-customer">
                <option value="">Select</option>
                @foreach($customers as $customer)
                    @php $areaName = optional($customer->customer_category)->name; @endphp
                    <option value="{{ $customer->id }}" data-due-limit="{{ $customer->due_limit }}" data-current-balance="{{ $customer->balance }}" data-customer-area="{{ $areaName ?? '' }}" {{ $customer->is_default ? 'selected' : '' }}>{{ $customer->customerId }} - {{ $customer->name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}{{ $areaName ? ' - ' . $areaName : '' }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3 px-1">
        <div class="form-group">
            <label class="font-weight-bold">Customer Area</label>
            <input type="text" class="form-control customer-area-input" placeholder="Customer Area" readonly style="background-color: #e9ecef; border: 1px solid #ccc;">
        </div>
    </div>
    <div class="col-md-3 px-1">
        <div class="form-group">
            <label class="font-weight-bold">Supplier Name{!! request()->is('*supplier-wise*') ? '<span class="text-danger">*</span>' : '' !!}</label>
            <select name="supplier_id" id="supplier_id" class="form-control select2">
                <option value="">Select Supplier</option>
                @foreach($suppliers as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3 pl-1">
        <div class="form-group">
            <label class="font-weight-bold">Sale Date</label>
            <input type="date" name="sale_date" class="form-control" value="{{ date('Y-m-d') }}">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5 pr-1">
        <div class="form-group">
            <label class="font-weight-bold">Marketers Name<i class="fa fa-user pl-1" style="font-size: 10px;"></i></label>
            <select name="marketers_id" class="form-control select2">
                <option value="">Select</option>
                @foreach($marketers as $marketer)
                    <option value="{{ $marketer->id }}">{{ $marketer->marketers_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-7 pl-1">
        <div class="form-group">
            <label class="font-weight-bold">Note</label>
            <textarea name="note" class="form-control" placeholder="Note" rows="1" style="height: 30px; min-height: 30px;"></textarea>
        </div>
    </div>
</div>