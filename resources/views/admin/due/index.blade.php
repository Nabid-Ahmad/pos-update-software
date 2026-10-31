@extends('admin.master')

@section('content')
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div>
                        <h3 class="float-left"><i class="fa fa-th-list"></i> {{ ucfirst(request('type')) }} Payment List</h3>

                        @if(hasPermission('collections.create'))
                            <a href="/due-collections/create?type={{ request('type') }}" class="btn btn-primary float-right" ><i class="fa fa-plus"></i>  Add new </a>
                        @endif
                        <div class="clearfix"></div>
                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <form action="" method="get">
                                    <input type="hidden" name="type" value="{{ request('type') }}">
                                    
                                    <div class="form-row align-items-center">
                                        <!-- Party Filter -->
                                        <div class="col-auto mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">{{ ucfirst(request('type')) }}</span></div>
                                                <div style="width: 200px;">
                                                    <select name="party_id" class="form-control select2" style="width: 100%;">
                                                        <option value="">-All-</option>
                                                        @foreach($parties as $id => $name)
                                                            <option value="{{ $id }}" {{ request('party_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Supplier Filter -->
                                        @if(isset($suppliers) && $suppliers->count() > 0)
                                        <div class="col-auto mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">Supplier</span></div>
                                                <div style="width: 200px;">
                                                    <select name="supplier_id" class="form-control select2" style="width: 100%;">
                                                        <option value="">-All-</option>
                                                        @foreach($suppliers as $id => $name)
                                                            <option value="{{ $id }}" {{ request('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Search text -->
                                        <div class="col-auto mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">Search</span></div>
                                                <input type="text" class="form-control" name="search" placeholder="Inv/Code..." value="{{ request('search') }}" style="width: 120px;">
                                            </div>
                                        </div>

                                        <!-- Date From -->
                                        <div class="col-auto mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">From</span></div>
                                                <input type="text" class="form-control dateField" name="start_date" placeholder="YYYY-MM-DD" value="{{ request('start_date') }}" style="width: 100px;">
                                            </div>
                                        </div>
                                        
                                        <!-- Date To -->
                                        <div class="col-auto mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">To</span></div>
                                                <input type="text" class="form-control dateField" name="end_date" placeholder="YYYY-MM-DD" value="{{ request('end_date') }}" style="width: 100px;">
                                            </div>
                                        </div>
                                        
                                        <!-- Buttons -->
                                        <div class="col-auto mb-2">
                                            <button style="cursor: pointer" type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                            <a href="{{ route('due-collections.index', ['type' => request('type')]) }}" class="btn btn-danger"><i class="fa fa-times"></i></a>
                                        </div>

                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="tile-body table-responsive table-sm">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="1%">#</th>
                                    <th width="10%">Invoice ID</th>
                                    <th width="10%">Date</th>
                                    <th>{{ ucfirst(request('type')) }} Name</th>
                                    @if(request('type') == 'customer')
                                    <th>Supplier</th>
                                    @endif
                                    <th>Account</th>
                                    <th>Payment Method</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th width="1%">Approval</th>
                                    <th width="5%" class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($reviews as $key => $review)
                                    <tr>
                                        <td>{{ $key + $reviews->firstItem() }}</td>
                                        <td>{{ $review->invoiceId }}</td>
                                        <td>{{ $review->date }}</td>
                                        <td>{{ optional($review->transactionable)->name }}</td>
                                        @if(request('type') == 'customer')
                                        <td>{!! $review->fk_supplier_id ? '<span class="badge badge-info">' . optional($review->supplier)->name . '</span>' : '<span class="badge badge-secondary">General</span>' !!}</td>
                                        @endif
                                        <td>{{ optional($review->account)->account_name }}</td>
                                        <td>{{ ucwords(optional($review->payment_method)->method_name) }}</td>
                                        <td>
                                            @if($review->isSupplier())
                                                @if($review->type == 'refund' || $review->amount > 0)
                                                    <span class="badge badge-success"><i class="fa fa-arrow-down"></i> Receive / Refund</span>
                                                @else
                                                    <span class="badge badge-danger"><i class="fa fa-arrow-up"></i> Payment</span>
                                                @endif
                                            @else
                                                <span class="badge badge-success"><i class="fa fa-arrow-down"></i> Receive</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format(abs($review->amount),2) }}</td>
                                        <td>
                                            @if (!$review->approved_at)
                                                <form action="{{ route('due-collections.approve', $review->id) }}"
                                                      onsubmit="disableButton(this)"
                                                      method="post">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-corner">
                                                <span class="btn btn-info btn-sm popover-success"
                                                      data-toggle="popover"
                                                      data-trigger="hover"
                                                      data-placement="top"
                                                      title="<i class='ace-icon fa fa-info-circle text-success'></i> Log Information"
                                                      data-content="<p>Created By: {{ ucwords(optional($review->created_user)->name) ?? 'System Admin' }}.</p> <p> Created At : {{ fdate($review->created_at, 'Y-m-d  h:i A') }} </p>
                                                       <hr/>
                                                       <p>Approved By: {{ ucwords(optional($review->approved_user)->name) ?? 'System Admin' }}.</p> <p> Updated At : {{ fdate($review->approved_at, 'Y-m-d  h:i A') }} </p>">
                                                    <i class="fa fa-info-circle"></i>
                                                </span>

                                                <a class="btn btn-primary btn-sm" href="{{ route('due-collections.show', $review->id) }}?type={{ request('type') }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                @if(hasPermission('collections.destroy'))
                                                    <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" onclick="deleteData({{ $review->id }})" data-target="#DeleteModal">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @include('admin.includes.pagination', ['data' => $reviews])
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('admin.includes.delete_confirm')
@endsection
@section('footer-script')
    <script type="text/javascript">
        function deleteData(id) {
            var id = id;
            var url = '{{ route("customer-collections.destroy", ":id") }}';
            url = url.replace(':id', id);
            $("#deleteForm").attr('action', url);
        }
        function formSubmit() {
            $("#deleteForm").submit();
        }
        function disableButton(element){
            $(element).find('button').attr('disabled', true)
        }
    </script>
@stop
