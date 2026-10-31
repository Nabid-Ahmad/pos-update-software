
<div class="row mb-2 ml-2 mt-2">
    <div class="col-md-3 pl-0">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="input-group-text bg-transparent border-0 font-weight-bold">Supplier</span>
            </div>
            <div style="flex: 1;">
                <select name="fk_supplier_id" required class="form-control select-supplier select2" style="width: 100%">
                    <option value="">Select</option>
                    @foreach($suppliers as $id => $supplier)
                        <option value="{{ $id }}" {{ old('fk_supplier_id') == $id ? 'selected' : '' }}>{{ $supplier }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-3 pl-0">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="input-group-text bg-transparent border-0 font-weight-bold">Warehouse</span>
            </div>
            <div style="flex: 1;">
                <select name="warehouse_id" class="form-control select-warehouse select2" style="width: 100%">
                    <option value="" selected>Show Room</option>
                    @foreach($warehouses as $id => $warehouse)
                        <option value="{{ $id }}" {{ old('warehouse_id') == $id ? 'selected' : '' }}>{{ $warehouse }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-3 pl-0">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="input-group-text bg-transparent border-0 font-weight-bold">Date</span>
            </div>
            <input name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" class="form-control dateField" type="text" placeholder="Date" style="flex: 1;">
        </div>
    </div>

    <div class="col-md-3 pl-0">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="input-group-text bg-transparent border-0 font-weight-bold">Chalan No</span>
            </div>
            <input name="chalan_no" value="{{ old('chalan_no') }}" class="form-control" type="text" placeholder="Chalan No" style="flex: 1;">
        </div>
    </div>

    <br>
    <br>
    <br>
</div>
