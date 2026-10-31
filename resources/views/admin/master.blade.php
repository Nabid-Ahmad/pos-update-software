<!DOCTYPE html>

<html lang="en">
    <head class="d-print-none">
        <meta name="description" content="">

        <title>{{ config('app.project_title', 'POS Update Software') }} - @yield('title', 'Dashboard')</title>
        <meta name="title" content="POS Update Software"/>
        <meta name="Author" content="POS Software"/>
        <meta name="Rating" content="General"/>
        <meta name="distribution" content="Global"/>

        <!-- Completely Transparent / Blank Favicon -->
        <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=">
        <link rel="shortcut icon" type="image/x-icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=">

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
        <!-- Font-icon css-->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/admin/css/modern-theme.css') }}?v=3.3">

        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sweetalert2.min.css') }}">

        @stack('style')
        <style>
            .datepicker {
                z-index: 99999 !important;
            }

            li.treeview {
                border: none !important;
            }
            
            .app-sidebar {
                overflow-y: auto !important;
                max-height: 100vh !important;
                padding-bottom: 60px !important;
            }
            
            /* Fix for long dropdowns getting cut off */
            .treeview.is-expanded .treeview-menu {
                max-height: 3000px !important;
            }
            
            .app-sidebar::-webkit-scrollbar {
                width: 6px !important;
                display: block !important;
            }
            .app-sidebar::-webkit-scrollbar-track {
                background: #080c15 !important; 
            }
            .app-sidebar::-webkit-scrollbar-thumb {
                background: #334155 !important; 
                border-radius: 4px !important;
            }
            .app-sidebar::-webkit-scrollbar-thumb:hover {
                background: #475569 !important; 
            }
            
            /* Unified Print Preview Mode Styles */
            body.print-view-mode {
                background: #ffffff !important;
                padding-top: 60px !important;
            }
            body.print-view-mode .app-header,
            body.print-view-mode .app-sidebar,
            body.print-view-mode .no-print,
            body.print-view-mode .filter-area,
            body.print-view-mode .row.d-print-none,
            body.print-view-mode .d-print-none,
            body.print-view-mode footer {
                display: none !important;
            }
            body.print-view-mode .app-content {
                margin-left: 0 !important;
                padding: 15px !important;
                background-color: #ffffff !important;
                min-height: auto !important;
                width: 100% !important;
            }
        </style>

        @yield('css')
    </head>

@php
    // Automatically trigger database migrations if they haven't been run yet on production
    if (!\Illuminate\Support\Facades\Schema::hasColumn('software_payments', 'trx_id') || !\Illuminate\Support\Facades\Schema::hasColumn('companies', 'invoice_footer')) {
        try {
            \Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            // Silence to avoid breaking page load
        }
    }

    $hasMultipleCompany = \App\Company::count() > 1 ? true : false;
    $settings       = \App\SoftwareSetting::companies()->get();
    $company        = App\Company::where('id', 1)->first() ?? new App\Company();

    // Check for any unpaid payment
    $unpaid_payment = App\SoftwarePayment::where('status', 0)
             ->orderBy('software_payment_date','ASC')->first();

    $is_locked = false;
    $in_grace_period = false;
    $in_alert_period = false;
    $grace_days_left = 0;
    $lock_date = null;
    $due_date = null;
    $alert_date = null;
    $grace_period_days = 0;

    if ($unpaid_payment && !request()->is('software_payments*')) {
        $due_date = Carbon\Carbon::parse($unpaid_payment->software_payment_date);
        $grace_period_days = $unpaid_payment->grace_period ?? 0;
        $alert_days_before = $unpaid_payment->alert ?? 0;
        $lock_date = $due_date->copy()->addDays($grace_period_days);
        $alert_date = $due_date->copy()->subDays($alert_days_before);
        $today = Carbon\Carbon::today();

        if ($today->gt($lock_date)) {
            $is_locked = true;
        } elseif ($today->gt($due_date)) {
            $in_grace_period = true;
            $grace_days_left = $today->diffInDays($lock_date, false);
            if ($grace_days_left < 0) {
                $grace_days_left = 0;
            }
        } elseif ($today->greaterThanOrEqualTo($alert_date) && $today->lte($due_date)) {
            $in_alert_period = true;
        }
    }

    // Always empty out $tests for sidebar so all menu items remain fully visible and clickable
    $tests = '';
@endphp
    <body class="app sidebar-mini rtl {{ (request('print') == 'pdf' || request('print') == 'excel') ? 'print-view-mode' : '' }}" translate="no">
        
        @if(request('print') == 'pdf' || request('print') == 'excel')
            <!-- Standalone Preview Control Bar -->
            <div id="print-top-bar" style="position: fixed; top: 0; left: 0; right: 0; height: 60px; background: #222d32; color: #fff; z-index: 999999; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); font-family: 'Outfit', 'Inter', sans-serif;" class="no-print">
                <div style="font-weight: bold; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                    <span style="color: #e52b27; font-size: 20px;">📄</span> Standalone Report Preview
                </div>
                <div style="display: flex; gap: 12px;">
                    <button onclick="window.print()" class="btn btn-success btn-sm" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; font-weight: bold;">
                        <i class="fa fa-print"></i> Print Report
                    </button>
                    <button onclick="triggerPDFDownload()" class="btn btn-danger btn-sm" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; font-weight: bold;">
                        <i class="fa fa-file-pdf-o"></i> Download PDF
                    </button>
                    <button onclick="triggerExcelDownload()" class="btn btn-success btn-sm" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; font-weight: bold; background-color: #1f7244; border-color: #1f7244;">
                        <i class="fa fa-file-excel-o"></i> Download Excel
                    </button>
                    <button onclick="window.close()" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px; padding: 6px 14px; font-weight: bold;">
                        <i class="fa fa-times"></i> Close Tab
                    </button>
                </div>
            </div>
            
            <script>
                // Load html2pdf dynamically if not present
                if (typeof html2pdf === 'undefined') {
                    var script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                    document.head.appendChild(script);
                }
                
                function triggerPDFDownload() {
                    var element = document.getElementById('stock_table') || document.querySelector('.table-responsive') || document.querySelector('main');
                    var opt = {
                        margin:       [0.4, 0.4, 0.4, 0.4],
                        filename:     'Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                        image:        { type: 'jpeg', quality: 0.98 },
                        html2canvas:  { scale: 2 },
                        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
                    };
                    
                    // Trigger PDF download
                    html2pdf().set(opt).from(element).save();
                }

                function triggerExcelDownload() {
                    var table = document.querySelector('table.table-bordered') || document.querySelector('table');
                    if (!table) return;
                    
                    var html = table.outerHTML;
                    var excelFile = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><!--[if gte mso 9]><xml><" + "x:ExcelWorkbook><" + "x:ExcelWorksheets><" + "x:ExcelWorksheet><" + "x:Name>Report</" + "x:Name><" + "x:WorksheetOptions><" + "x:DisplayGridlines/></" + "x:WorksheetOptions></" + "x:ExcelWorksheet></" + "x:ExcelWorksheets></" + "x:ExcelWorkbook></xml><![endif]--></head><body>";
                    excelFile += html;
                    excelFile += "</body></html>";
                    
                    var blob = new Blob([excelFile], { type: 'application/vnd.ms-excel;charset=utf-8;' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'Report_' + new Date().toISOString().slice(0,10) + '.xls';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }

                window.addEventListener('load', function() {
                    var printParam = new URLSearchParams(window.location.search).get('print');
                    if (printParam === 'excel') {
                        setTimeout(triggerExcelDownload, 500);
                    } else if (printParam === 'pdf') {
                        window.print();
                    }
                });
            </script>
        @endif

        @include('admin.includes.header')

        @if($in_alert_period && !$is_locked && !$in_grace_period)
        <div class="alert text-center d-print-none" role="alert" style="z-index: 9999; background-color: #ff9800; border-color: #ffffff; color: #ffffff; margin: 0; padding: 10px; position: fixed; top: 0; left: 0; right: 0; box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);">
            <b style="font-size: 16px;">
                আপনার সফটওয়্যার সাবস্ক্রিপশনের মেয়াদ আগামী {{ $due_date->format('d M, Y') }} এ শেষ হতে যাচ্ছে। নিরবচ্ছিন্ন সেবা পেতে অনুগ্রহ করে {{ $unpaid_payment->amount ?? $unpaid_payment->paid_amount }} টাকা পরিশোধ করুন।
            </b>
            <form action="{{ route('bkash.create') }}" method="POST" style="display: inline-block; margin-left: 15px;">
                @csrf
                <input type="hidden" name="order_id" value="{{ $unpaid_payment->id }}">
                <input type="hidden" name="amount" value="{{ $unpaid_payment->amount ?? $unpaid_payment->paid_amount }}">
                <button type="submit" class="btn btn-sm btn-light" style="font-weight: bold; color: #e52b27; padding: 4px 12px; border-radius: 20px;">
                    <img src="https://wp.logos-download.com/wp-content/uploads/2022/01/BKash_Logo_icon-700x662.png" alt="Bkash" style="width: 16px; margin-right: 5px;"> Pay Now
                </button>
            </form>
        </div>
        <style>
            .app-header { top: 45px !important; }
            .app-sidebar { margin-top: 45px !important; }
            .app-content { margin-top: 45px !important; }
        </style>
        @endif

        @if($in_grace_period && !$is_locked)
        <div class="alert text-center d-print-none" role="alert" style="z-index: 9999; background-color: #ff3333; border-color: #ffffff; color: #ffffff; margin: 0; padding: 10px; position: fixed; top: 0; left: 0; right: 0; box-shadow: 0 4px 10px rgba(255, 51, 51, 0.3);">
            <b style="font-size: 16px;">
                আপনার সফটওয়্যারটির মেয়াদ শেষ হয়েছে! আপনার সিস্টেমটি আর মাত্র <span id="grace-countdown"></span> পর লক হয়ে যাবে। সিস্টেম সচল রাখতে এখনই পে করুন।
            </b>
            <form action="{{ route('bkash.create') }}" method="POST" style="display: inline-block; margin-left: 15px;">
                @csrf
                <input type="hidden" name="order_id" value="{{ $unpaid_payment->id }}">
                <input type="hidden" name="amount" value="{{ $unpaid_payment->amount ?? $unpaid_payment->paid_amount }}">
                <button type="submit" class="btn btn-sm btn-light" style="font-weight: bold; color: #e52b27; padding: 4px 12px; border-radius: 20px;">
                    <img src="https://wp.logos-download.com/wp-content/uploads/2022/01/BKash_Logo_icon-700x662.png" alt="Bkash" style="width: 16px; margin-right: 5px;"> Pay {{ $unpaid_payment->amount ?? $unpaid_payment->paid_amount }} BDT
                </button>
            </form>
        </div>
        <script>
            var lockDate = new Date("{{ $lock_date->format('Y-m-d 23:59:59') }}").getTime();
            var countdownInterval = setInterval(function() {
                var now = new Date().getTime();
                var distance = lockDate - now;
                if (distance <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById("grace-countdown").innerHTML = "0 দিন, 0 ঘণ্টা, 0 মিনিট";
                    // Only reload if the lock date actually passed right now (prevents infinite reload)
                    if (!window.hasTriggeredLockReload) {
                        window.hasTriggeredLockReload = true;
                        location.reload(); 
                    }
                } else {
                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    document.getElementById("grace-countdown").innerHTML = days + " দিন, " + hours + " ঘণ্টা, " + minutes + " মিনিট";
                }
            }, 1000);
        </script>
        <style>
            .app-header { top: 45px !important; }
            .app-sidebar { margin-top: 45px !important; }
            .app-content { margin-top: 45px !important; }
        </style>
        @endif

        <!-- alert Start -->
        <div class="d-print-none app-title bg-danger text-white" id="alert_module"
            style="width: auto;
                display:none;
                margin-top: 50px;
                text-align: center;
                padding: 0px !important;
                margin-bottom: 0px !important;">
            @include('admin.includes.alert')
            @if (\Session::has('error'))
                <div class="alert alert-danger">
                    <ul>
                        <li>{!! \Session::get('error') !!}</li>
                    </ul>
                </div>
            @elseif (\Session::has('success'))
                <div class="alert alert-success">
                    <ul>
                        <li>{!! \Session::get('success') !!}</li>
                    </ul>
                </div>
            @else
            @endif
        </div>
        <!-- Sidebar menu-->
        @include('admin.includes.sidebar')


        @yield('content')

        @if($is_locked)
        <div id="subscription-lock-overlay" style="position: fixed; top: 0; left: 230px; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); z-index: 999999; display: flex; align-items: center; justify-content: center; font-family: 'Outfit', 'Inter', sans-serif;">
            <div style="background: #ffffff; padding: 40px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); text-align: center; max-width: 500px; width: 90%; border: 1px solid rgba(229, 43, 39, 0.2); animation: fadeInUp 0.5s ease;">
                <div style="font-size: 64px; margin-bottom: 20px; color: #e52b27; animation: pulse 2s infinite;">
                    🔒
                </div>
                <h2 style="color: #222d32; font-weight: 800; margin-bottom: 12px; font-size: 26px; letter-spacing: -0.5px;">Subscription Expired</h2>
                <p style="color: #666; font-size: 15px; line-height: 1.6; margin-bottom: 28px;">
                    আপনার সফটওয়্যার সাবস্ক্রিপশন মেয়াদ এবং গ্রেস পিরিয়ড সম্পূর্ণ শেষ হয়েছে! সচল রাখতে অনুগ্রহ করে বকেয়া বিল পরিশোধ করুন।
                </p>
                
                <div style="background: #fdf2f2; padding: 15px; border-radius: 12px; margin-bottom: 28px; border: 1px solid #fde2e2; text-align: left;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                        <span style="color: #666;">Due Date (বকেয়া তারিখ):</span>
                        <span style="font-weight: bold; color: #222d32;">{{ $unpaid_payment->software_payment_date }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px;">
                        <span style="color: #666;">Amount (টাকার পরিমাণ):</span>
                        <span style="font-weight: bold; color: #e52b27; font-size: 16px;">{{ $unpaid_payment->amount }} TK</span>
                    </div>
                </div>

                <form action="{{ route('bkash.create') }}" method="post" style="display: inline-block; width: 100%;">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $unpaid_payment->id }}">
                    <input type="hidden" name="amount" value="{{ $unpaid_payment->amount }}">
                    
                    <button type="submit" style="background: linear-gradient(135deg, #e52b27 0%, #8e0e00 100%); color: #fff; border: none; padding: 14px 28px; font-size: 16px; font-weight: bold; border-radius: 12px; width: 100%; cursor: pointer; box-shadow: 0 8px 20px rgba(229, 43, 39, 0.3); transition: all 0.3s ease;">
                        💳 Click Here to Pay Now
                    </button>
                </form>
            </div>
        </div>

        <style>
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            /* Disable scrolling and clicking behind the overlay */
            body {
                overflow: hidden !important;
            }
            .app-content {
                pointer-events: none !important;
                user-select: none !important;
                filter: blur(10px);
            }
            /* But keep the overlay itself interactive */
            #subscription-lock-overlay, #subscription-lock-overlay * {
                pointer-events: auto !important;
            }
            @media (max-width: 767px) {
                #subscription-lock-overlay {
                    left: 0 !important;
                }
            }
        </style>
        @endif


        @include('admin.includes.footer')

        <!-- Essential javaScript for application to work-->
        <script src="{{ asset('js/app.js') }}"></script>

        <script type="text/javascript" src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>

        <!-- confirm delete dialog box -->
        <script type="text/javascript" src="{{ asset('assets/admin/jq/delete_confirm.js') }}"></script>

        @include('sweetalert::alert')


        <!-- Number format -->
        <script type="text/javascript">
            // Global Select2 Custom Sorter to prioritize Prefix Matches
            if ($.fn.select2 && $.fn.select2.defaults) {
                $.fn.select2.defaults.set('sorter', function(data) {
                    var term = $('.select2-search__field').val();
                    if (!term || $.trim(term) === '') {
                        return data;
                    }
                    term = $.trim(term).toLowerCase();
                    
                    return data.sort(function(a, b) {
                        var aText = (a.text || '').toLowerCase();
                        var bText = (b.text || '').toLowerCase();
                        
                        var aStarts = aText.indexOf(term) === 0;
                        var bStarts = bText.indexOf(term) === 0;
                        
                        if (aStarts && !bStarts) {
                            return -1;
                        }
                        if (!aStarts && bStarts) {
                            return 1;
                        }
                        
                        // Word boundary match (e.g. term starts a word in the text)
                        var aWordStarts = aText.indexOf(' ' + term) !== -1;
                        var bWordStarts = bText.indexOf(' ' + term) !== -1;
                        if (aWordStarts && !bWordStarts) {
                            return -1;
                        }
                        if (!aWordStarts && bWordStarts) {
                            return 1;
                        }
                        
                        return 0;
                    });
                });
            }

            function amountFormat(amount) {
                return (amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
            }

            $(function () {
                $('[data-toggle="popover"]').popover({html: true, container: 'body'})
            })


            function onlyNumber(evnt) {
                if (evnt.charCode == 13) {
                    evnt.preventDefault();
                }

                var str = evnt.target.value
                var n = str.includes(".")
                if (event.charCode == 46 && n) {
                    return false
                }

                return (evnt.charCode >= 46 && evnt.charCode <= 57) || event.charCode == 13
            }

            $('.only-number').keypress(function() {
                if (event.charCode == 13) {
                    event.preventDefault();
                }
                var str = $(this).val()
                var n = str.includes(".")
                if (event.charCode == 46 && n) {
                    return false
                }

                return (event.charCode >= 46 && event.charCode <= 57) || event.charCode == 13
            })
            window.setTimeout(function () {
                $(".alert-danger, .alert-success").fadeTo(1000, 0).slideUp(500, function () {
                    $(this).remove();
                });
            }, 3000);

            // $('#sampleTable').DataTable({
            //     "bPaginate": false,
            //     "searching": false,
            //     order: []
            // });


            $('.dateField').datepicker({
                format: "yyyy-mm-dd",
                autoclose: true,
                todayHighlight: true
            });


            $('.select2').select2();
        </script>

        @include('admin.includes.keyboard-shortcut')

        <script type="text/javascript">
            $(document).ready(function() {
                // Fetch Low Stock Alerts
                $.ajax({
                    url: "{{ url('reports/product_alert/ajax') }}",
                    type: "GET",
                    success: function(response) {
                        if(response.count > 0) {
                            $('.not-badge').text(response.count);
                            $('.titleCount').text('You have ' + response.count + ' new notifications');
                            
                            let html = '';
                            $.each(response.products, function(index, product) {
                                html += '<li><a class="app-notification__item" href="{{ url("reports/product_alert") }}"><span class="app-notification__icon"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x text-danger"></i><i class="fa fa-exclamation-triangle fa-stack-1x fa-inverse"></i></span></span><div><p class="app-notification__message" style="font-size: 12px; font-weight:bold;">' + product.stock_product.product_name + ' is low stock!</p><p class="app-notification__meta">Available: ' + product.available_quantity + '</p></div></a></li>';
                            });
                            $('.temp').html(html);
                        } else {
                            $('.not-badge').hide();
                            $('.titleCount').text('No new notifications');
                            $('.temp').html('<li><a class="app-notification__item" href="javascript:;"><div class="text-center w-100">Stock is good</div></a></li>');
                        }
                    }
                });
            });
        </script>

        <script type="text/javascript">
            const fullUrl = "{{ request()->fullUrl() }}"
            let treeViewItem = document.querySelector('a.treeview-item.active')
            if (treeViewItem && treeViewItem.href === fullUrl) {
                treeViewItem.parentElement.parentElement.parentElement.classList.add('is-expanded')
            }
        </script>

        @yield('js')

        @section('footer-script')
        @show
    </body>
</html>
