@extends('admin.master')
@section('title', ' - Product List')

@section('content')

    <main class="app-content">
        <div class="row">
            <div class="col-md-12">

                <div class="tile">
                    @if(hasPermission('products.create'))
                        <a href="{{ route('products.create') }}" class="btn btn-primary no-print" style="float: right;">
                            <i class="fa fa-plus"></i> Add Product
                        </a>
                    @endif
                                    @if(hasPermission('products.destroy'))
                                        <button class="btn btn-danger no-print" id="bulkDeleteBtn" style="float: right; margin-right: 10px; display: none;">
                                            <i class="fa fa-trash"></i> Delete Selected
                                        </button>
                                    @endif

                    @include('partials._alert_message')

                    <h3 class="tile-title"><i class="fa fa-list"></i> Product List </h3>

                    <!-- Product Search Filter -->
                    @include('admin.product.products.includes.filter-data')

                    <div class="tile-body table-responsive">
                        <table class="table table-hover table-bordered table-sm" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="1%" class="no-print">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Sl.</th>
                                    <th>Supplier</th>
                                    <th>Name</th>
                                    <th>Product Code</th>
                                    <th>Barcode</th>
                                    <th>Unit</th>
                                    <th>Serial</th>
                                    <th class="text-right">Purchase Price</th>
                                    <th class="text-right">Sale Price</th>
                                    <th class="text-center">Image</th>
                                    <th width="10%" class="text-center no-print">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($products as $key => $product)
                                    <tr>
                                        <td class="no-print">
                                            <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
                                        </td>
                                        <td>{{ $key + (method_exists($products, 'firstItem') ? $products->firstItem() : 1) }}</td>
                                        <td>{{ optional($product->supplier)->name }}</td>
                                        <td>{{ $product->product_name }}</td>
                                        <td>{{ $product->product_code }}</td>
                                        <td>{{ optional($product->barcode)->barcode_number}}</td>
                                        <td>{{ $product->product_unit->name }}</td>
                                        <td>
                                            @if ($product->has_serial==1)
                                                {{-- <span class="badge badge-success">
                                                </span> --}}
                                                <a href="{{ route('serial-product.stocks') }}?product_id={{ $product->id }}&warehouse_id={{ request('warehouse_id') }}" target="__blank" class="btn btn-sm btn-success">Yes</a>
                                            @else
                                                <span class="badge badge-danger">No</span>
                                            @endif
                                        </td>
                                        <td class="text-right">{{ $product->product_cost }}</td>
                                        <td class="text-right">{{ $product->product_price }}</td>
                                        <td class="text-center">
                                            @if (file_exists($product->image))
                                                <img width="80px" height="50px" src="{{ asset($product->image) }}">
                                            @elseif (file_exists($product->images))
                                                <img width="80px" height="50px" src="{{ asset($product->images) }}">
                                            @endif
                                        </td>
                                        <td class="text-center no-print">
                                            @include('admin.product.products.includes.actions')
                                        </td>
                                    </tr>
                                @endforeach

                                @include('admin.includes.delete_confirm')
                            </tbody>
                        </table>

                            <div class="d-flex justify-content-between align-items-center mt-3 no-print">
                                <div>
                                    <style>
                                        .download-icon-btn {
                                            transition: transform 0.2s ease, filter 0.2s ease;
                                            cursor: pointer;
                                            display: inline-block;
                                            border: none;
                                            background: none;
                                            padding: 0;
                                            outline: none !important;
                                            box-shadow: none !important;
                                        }
                                        .download-icon-btn:hover {
                                            transform: scale(1.1);
                                            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));
                                        }
                                        .download-icon-btn:focus,
                                        .download-icon-btn:active {
                                            outline: none !important;
                                            box-shadow: none !important;
                                        }
                                    </style>
                                    
                                    <!-- XLS Icon -->
                                    <button type="submit" form="productFilterForm" name="print" value="excel" formtarget="_blank" class="download-icon-btn" title="Download Excel">
                                        @include('admin.reports.includes.excel_icon')
                                    </button>

                                    <!-- PDF Icon -->
                                    <button type="submit" form="productFilterForm" name="print" value="pdf" formtarget="_blank" class="download-icon-btn" title="Download PDF">
                                        @include('admin.reports.includes.pdf_icon')
                                    </button>
                                </div>
                                <div>
                                    @include('admin.includes.pagination', ['data' => $products])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>


        <script src="{{ asset('assets/admin/js/jquery-3.2.1.min.js') }}"></script>

        <script type="text/javascript">
            function deleteData(id) {
                var id = id;
                var url = '{{ route("products.destroy", ":id") }}';
                url = url.replace(':id', id);
                $("#deleteForm").attr('action', url);
            }
            function formSubmit() {
                $("#deleteForm").submit();
            }

            $(document).ready(function() {
                // Select all functionality
                $('#selectAll').on('change', function() {
                    $('.product-checkbox').prop('checked', $(this).prop('checked'));
                    toggleBulkDeleteBtn();
                });

                // Individual checkbox functionality
                $('.product-checkbox').on('change', function() {
                    if ($('.product-checkbox:checked').length === $('.product-checkbox').length) {
                        $('#selectAll').prop('checked', true);
                    } else {
                        $('#selectAll').prop('checked', false);
                    }
                    toggleBulkDeleteBtn();
                });

                function toggleBulkDeleteBtn() {
                    if ($('.product-checkbox:checked').length > 0) {
                        $('#bulkDeleteBtn').show();
                    } else {
                        $('#bulkDeleteBtn').hide();
                    }
                }

                // Bulk delete action
                $('#bulkDeleteBtn').on('click', function() {
                    if (confirm('Are you sure you want to delete the selected products?')) {
                        let selectedIds = [];
                        $('.product-checkbox:checked').each(function() {
                            selectedIds.push($(this).val());
                        });

                        $.ajax({
                            url: '{{ route("products.bulk-delete") }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ids: selectedIds
                            },
                            success: function(response) {
                                if (response.status) {
                                    location.reload();
                                } else {
                                    alert(response.message || 'Error deleting products.');
                                }
                            },
                            error: function() {
                                alert('An error occurred while deleting products.');
                            }
                        });
                    }
                });

            });
        </script>
@endsection
