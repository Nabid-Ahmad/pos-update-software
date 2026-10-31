@extends('admin.master')
@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                @include('partials._alert_message')
                <div class="tile">
                    <a href="{{route('product-damages.index')}}" class="btn btn-primary pull-right" style="float: right;"><i
                                class="fa fa-eye"></i> View Damages</a>
                    <h3 class="tile-title">New Product Damage</h3>
                    <hr>
                    <div class="tile-body">
                        <form class="form-horizontal" method="POST" action="{{ route('product-damages.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="control-label col-md-3">Reference No</label>
                                        <div class="col-md-8">
                                            <input name="reference" value="{{ old('reference') }}"
                                                   class="form-control" type="text" placeholder="Reference">
                                            <div class="text-danger">
                                                {{ $errors->has('reference') ? $errors->first('reference'):'' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="control-label col-md-3">Date</label>
                                        <div class="col-md-8">
                                            <input name="date" value="{{ old('date') ?? date('Y-m-d') }}"
                                                   class="form-control dateField" type="text" placeholder="Date">
                                            <div class="text-danger">
                                                {{ $errors->has('date') ? $errors->first('date') : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <td width="30%">Product Name</td>
                                                <td width="12%">Available Stock</td>
                                                <td>Description</td>
                                                <td width="8%">Type</td>
                                                <td width="18%">Damaged Quantity</td>
                                                <td width="1%">Action</td>
                                            </tr>
                                        </thead>

                                        <tbody class="r-group">
                                        <tr class="r-row">
                                            <td>
                                                <select class="form-control product_name" name="product_id[]"></select>
                                            </td>
                                            <td>
                                                <input type="text" name="available_qty[]" class="form-control available-quantity text-center font-weight-bold" value="0" placeholder="Available" readonly style="color: #007bff; background: #eef5ff;">
                                            </td>
                                            <td>
                                                <textarea name="description[]" class="form-control" placeholder="Description" rows="1"></textarea>
                                            </td>
                                            <td>
                                                <select name="type[]" class="form-control" style="height: 33px">
                                                    <option value="expired">Expired</option>
                                                    <option value="damaged" selected>Damaged</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input value="0" class="form-control damage-quantity text-center font-weight-bold" name="quantity[]" type="number" step="any" min="0" placeholder="Damaged Qty">
                                                <span class="stock-warning-msg text-danger font-weight-bold" style="font-size: 11px; display: none;"></span>
                                            </td>
                                            <td>
                                                <button type="button" onclick="addRow()" tabindex="-1" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        </tbody>

                                    </table>
                                    <div class="tile-footer">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <button class="btn btn-primary pull-right" type="submit"><i
                                                            class="fa fa-fw fa-lg fa-check-circle"></i>Add Damage
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection

@section('footer-script')
    <script src="{{asset('jq/select2Loads.js')}}"></script>
    <script>
        function triggerSelect(){
            select2Loads({
                selector: '.product_name',
                url: '/saleable-products'
            });
        }
        triggerSelect();

        function addRow()
        {
            const htmlString = `
            <tr class="r-row">
                <td>
                    <select class="form-control product_name" name="product_id[]"></select>
                </td>
                <td>
                    <input type="text" name="available_qty[]" class="form-control available-quantity text-center font-weight-bold" value="0" placeholder="Available" readonly style="color: #007bff; background: #eef5ff;">
                </td>
                <td>
                    <textarea name="description[]" class="form-control" placeholder="Description" rows="1"></textarea>
                </td>
                <td>
                    <select name="type[]" class="form-control" style="height: 33px">
                        <option value="expired">Expired</option>
                        <option value="damaged" selected>Damaged</option>
                    </select>
                </td>
                <td>
                    <input value="0" class="form-control damage-quantity text-center font-weight-bold" name="quantity[]" type="number" step="any" min="0" placeholder="Damaged Qty">
                    <span class="stock-warning-msg text-danger font-weight-bold" style="font-size: 11px; display: none;"></span>
                </td>
                <td>
                    <button type="button" onclick="deleteRow(this)" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
            `;
            $('.r-group').append(htmlString);
            triggerSelect();
        }

        function deleteRow(element)
        {
            $(element).parents('.r-row').remove();
        }

        // Fetch available quantity on product selection
        $(document).on('change', '.product_name', function (e) {
            let row = $(this).closest('.r-row');
            let productId = $(this).val();
            let availInput = row.find('.available-quantity');
            let qtyInput = row.find('.damage-quantity');
            let warningSpan = row.find('.stock-warning-msg');

            if (productId && parseFloat(productId)) {
                $.get(`/sales/available_quantity/${productId}`, function (data) {
                    let avail = parseFloat(data.available_quantity) || 0;
                    availInput.val(avail);
                    let currentQty = parseFloat(qtyInput.val()) || 0;

                    if (avail <= 0) {
                        qtyInput.val(0);
                        warningSpan.text('⚠ Stock নেই (0)! Damage এন্ট্রি করা যাবে না।').show();
                    } else if (currentQty > avail) {
                        qtyInput.val(avail);
                        warningSpan.text('⚠ Stock এর বেশি দেওয়া যাবে না! সর্বোচ্চ: ' + avail).show();
                    } else {
                        warningSpan.hide();
                    }
                });
            } else {
                availInput.val(0);
                qtyInput.val(0);
                warningSpan.hide();
            }
        });

        // Validate quantity on input/keyup/change
        $(document).on('input keyup change', '.damage-quantity', function () {
            validateQuantity(this);
        });

        function validateQuantity(element) {
            let row = $(element).closest('.r-row');
            let available = parseFloat(row.find('.available-quantity').val()) || 0;
            let quantity = parseFloat($(element).val()) || 0;
            let warningSpan = row.find('.stock-warning-msg');

            if (available <= 0) {
                $(element).val(0);
                warningSpan.text('⚠ Stock নেই (0)! Damage এন্ট্রি করা যাবে না।').show();
                return false;
            }

            if (quantity > available) {
                $(element).val(available);
                warningSpan.text('⚠ Stock এর বেশি দেওয়া যাবে না! সর্বোচ্চ: ' + available).show();
                return false;
            } else if (quantity < 0) {
                $(element).val(0);
                warningSpan.hide();
                return false;
            } else {
                warningSpan.hide();
                return true;
            }
        }

        // Form Submit Validation - Hard Block if invalid
        $('form').on('submit', function (e) {
            let valid = true;
            let hasItems = false;
            let rows = $('.r-group .r-row');

            if (rows.length === 0) {
                alert('Please add at least one product.');
                e.preventDefault();
                return false;
            }

            rows.each(function () {
                let row = $(this);
                let productId = row.find('.product_name').val();
                if (productId) {
                    hasItems = true;
                    let available = parseFloat(row.find('.available-quantity').val()) || 0;
                    let qty = parseFloat(row.find('.damage-quantity').val()) || 0;

                    if (available <= 0) {
                        alert('Stock নেই এমন প্রোডাক্ট Damage হিসেবে এন্ট্রি দেওয়া যাবে না!');
                        row.find('.damage-quantity').focus();
                        valid = false;
                        return false;
                    }

                    if (qty <= 0) {
                        alert('Damaged Quantity অবশ্যই ০ এর বেশি হতে হবে!');
                        row.find('.damage-quantity').focus();
                        valid = false;
                        return false;
                    }

                    if (qty > available) {
                        alert('Damaged Quantity (' + qty + ') Available Stock (' + available + ') এর চেয়ে বেশি হতে পারে না!');
                        row.find('.damage-quantity').val(available).focus();
                        valid = false;
                        return false;
                    }
                }
            });

            if (!hasItems) {
                alert('Please select at least one product.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
                return false;
            }
        });

        $(document).on('keypress', 'input:not(.damage-quantity)', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endsection
