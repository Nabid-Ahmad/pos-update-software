@extends('admin.master')

@section('title', ' - Purchase')

@push('style')
@endpush

@section('content')



    <!-- filter section -->
    @include('admin.purchase.purchases.includes._add_product_serial_modal')

    <main class="app-content">
        <form class="form-horizontal" id="purchaseForm" method="POST" action="{{ route('purchases.store') }}">
            @csrf
            <div class="row">
                <div class="col-sm-9">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="fa fa-plus"></i> Add New Purchases
                            <a href="#" onclick="showHoldPurchaseListModal()" class="btn btn-warning btn-sm mr-2" style="padding: 2px 8px; font-size: 12px; font-weight: bold;">
                                <i class="fa fa-list"></i> Hold List <span class="badge badge-light hold-purchase-count-badge">0</span>
                            </a>
                            <a href="{{ route('purchases.index') }}" class="btn btn-info btn-sm pull-right">
                                <i class="fa fa-eye"></i> Purchase List
                            </a>
                        </div>

                        <div class="card-body" style="padding-top: 5px;">


                            @include('partials._alert_message')


                            <!-- filter section -->
                            @include('admin.purchase.purchases.includes.filter-section')




                            <!-- product entry section -->
                            @include('admin.purchase.purchases.includes.product-entry')




                            <!-- product display section -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="3%">Sl.</th>
                                                <th>Product Name</th>
                                                <th>Product Code</th>
                                                {{-- <th>Expire Date</th> --}}
                                                <th width="100px">Product Qty</th>
                                                <th width="100px">Unit Cost</th>
                                                <th width="80px">Free Qty</th>
                                                <th width="100px">Subtotal</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>

                                        <tbody class="product-display">

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- Sidebar Total calculation -->
                @include('admin.purchase.purchases.includes.sidebar-total')
            </div>
        </form>
    </main>

    <!-- Hold Purchase List Modal — placed OUTSIDE the form to prevent form submission on Resume click -->
    <div class="modal fade" id="holdPurchaseListModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fa fa-list"></i> Held Purchases List</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Sl</th>
                                <th>Supplier</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Time</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="hold-purchase-list-tbody">
                            <!-- JS will populate this -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- @include('admin.includes.date_field') --}}
@endsection

@section('footer-script')
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="{{ asset('jq/loadDetails.js') }}"></script>

    <script type="text/javascript" src="{{ asset('assets/custom_js/toggle-full-screen.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/custom_js/individual-purchase-create.js') }}?{{ fdate(now(), 'Y-m-d H:i') }}"></script>
    <script  src="{{ asset('assets/admin/js/plugins/bootstrap-datepicker.min.js')}}"></script>

    <script>
        $('.dateField').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true
        });

        /* =============================================
         * Hold Purchase Feature (Local Storage)
         * ============================================= */
        function holdCurrentPurchase() {
            if ($('.product-display tr').length == 0) {
                if (typeof toastr !== 'undefined') { toastr.warning('Cart is empty! Please add products.'); }
                else { alert('Cart is empty!'); }
                return;
            }

            // Snapshot current input values
            $('.product-display tr input').each(function () {
                $(this).attr('value', $(this).val());
            });

            let supplierText = $('.select-supplier option:selected').text();
            if (!supplierText || supplierText.trim() === 'Select') {
                supplierText = 'Unknown Supplier';
            }

            let purchaseData = {
                id: Date.now(),
                time: new Date().toLocaleTimeString(),
                supplier_id: $('.select-supplier').val() || '',
                supplier_name: supplierText,
                warehouse_id: $('.select-warehouse').val() || '',
                purchase_date: $('[name="purchase_date"]').val() || '',
                chalan_no: $('[name="chalan_no"]').val() || '',
                cart_html: $('.product-display').html(),
                invoice_discount: $('#invoice_discount').val() || 0,
                invoice_tax: $('#invoice_tax').val() || 0,
                total_payable: $('#total_payable_temp').val() || 0,
                item_count: $('.product-display tr').length
            };

            let heldPurchases = JSON.parse(localStorage.getItem('held_purchases')) || [];
            heldPurchases.push(purchaseData);
            localStorage.setItem('held_purchases', JSON.stringify(heldPurchases));

            if (typeof toastr !== 'undefined') { toastr.success('Purchase Held Successfully!'); }
            else { alert('Purchase Held Successfully!'); }

            updateHoldPurchaseBadge();
            setTimeout(() => { window.location.reload(); }, 1000);
        }

        function showHoldPurchaseListModal() {
            let heldPurchases = JSON.parse(localStorage.getItem('held_purchases')) || [];
            let html = '';
            if (heldPurchases.length == 0) {
                html = '<tr><td colspan="6" class="text-center font-weight-bold text-danger">No Held Purchases Found</td></tr>';
            } else {
                heldPurchases.forEach((purchase, i) => {
                    html += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${purchase.supplier_name}</td>
                        <td class="text-center">${purchase.item_count}</td>
                        <td class="text-right font-weight-bold">৳ ${purchase.total_payable}</td>
                        <td class="text-center">${purchase.time}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-success" onclick="resumeHeldPurchase(${i})"><i class="fa fa-hand-o-right"></i> Resume</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteHeldPurchase(${i})"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#hold-purchase-list-tbody').html(html);
            $('#holdPurchaseListModal').modal('show');
        }

        function resumeHeldPurchase(index) {
            let heldPurchases = JSON.parse(localStorage.getItem('held_purchases')) || [];
            let purchase = heldPurchases[index];
            if (!purchase) return;

            $('.product-display').html(purchase.cart_html);

            if (purchase.supplier_id) {
                $('.select-supplier').val(purchase.supplier_id).trigger('change');
            }
            if (purchase.warehouse_id) {
                $('.select-warehouse').val(purchase.warehouse_id).trigger('change');
            }
            if (purchase.purchase_date) {
                $('[name="purchase_date"]').val(purchase.purchase_date);
            }
            if (purchase.chalan_no) {
                $('[name="chalan_no"]').val(purchase.chalan_no);
            }
            $('#invoice_discount').val(purchase.invoice_discount).trigger('keyup');
            $('#invoice_tax').val(purchase.invoice_tax).trigger('keyup');

            // Recalculate totals
            if (typeof calculateTotal === 'function') calculateTotal();
            if (typeof setItemSerial === 'function') setItemSerial();

            // Remove from list
            heldPurchases.splice(index, 1);
            localStorage.setItem('held_purchases', JSON.stringify(heldPurchases));

            updateHoldPurchaseBadge();
            $('#holdPurchaseListModal').modal('hide');
            if (typeof toastr !== 'undefined') { toastr.info('Held Purchase Resumed!'); }
        }

        function deleteHeldPurchase(index) {
            if (!confirm('Are you sure you want to delete this held purchase?')) return;
            let heldPurchases = JSON.parse(localStorage.getItem('held_purchases')) || [];
            heldPurchases.splice(index, 1);
            localStorage.setItem('held_purchases', JSON.stringify(heldPurchases));
            showHoldPurchaseListModal();
            updateHoldPurchaseBadge();
        }

        function updateHoldPurchaseBadge() {
            let heldPurchases = JSON.parse(localStorage.getItem('held_purchases')) || [];
            $('.hold-purchase-count-badge').text(heldPurchases.length);
            if (heldPurchases.length > 0) {
                $('.hold-purchase-count-badge').removeClass('badge-light').addClass('badge-danger');
            } else {
                $('.hold-purchase-count-badge').removeClass('badge-danger').addClass('badge-light');
            }
        }

        $(document).ready(function () {
            updateHoldPurchaseBadge();
        });
    </script>
@endsection
