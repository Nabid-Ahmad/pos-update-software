@extends('admin.master')
@section('title', ' - Dashboard')

@push('style')
    <style>
        th.dash-head {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            color: #ffffff !important;
            padding: 12px 18px !important;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            font-family: var(--theme-heading, 'Plus Jakarta Sans', sans-serif) !important;
        }

        th.dash-head h4 {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            margin: 0 !important;
            letter-spacing: -0.2px !important;
        }

        .color-red {
            color: #ef4444 !important;
        }


        .first_card{
            background: rgb(198,40,96);
            background: linear-gradient(100deg, rgba(198,40,96,1) 4%, rgba(126,74,48,0.7063200280112045) 41%, rgba(57,180,18,1) 87%);



        }

        /* NexPOS Dashboard Modern Cards Theme */
        /*/////////////////////////*/



        .card-section .card-item .item-body .load-data {
            font-family: 'Fira Sans', sans-serif;
        }



        .card-section .card-item .item-body .load-data,
        .card-section .card-item .dashboard-info strong,
        .card-section .card-item .dashboard-info h2,
        .card-section .card-item .dashboard-info i,


        .card-section .card-item .dashboard-info>h2>i {
            font-size: 38px;
        }


        .canvasjs-chart-toolbar>button {
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.25);
            border-radius: 3px;
            width: 30px !important;
            padding: 5px 7px !important;
        }


        /*<!----------- /DASHBOARD CHART CSS ----------->*/



        /*<!----------- DASHBOARD CARD CSS ----------->*/

        .card-section .card-item {
            margin-bottom: 4px !important;
            transition: 0.3s;
        }





        .card-section .card-item .dashboard-info strong.text-center {
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* .effect8 {
            -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
            -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
        } */

        .effect7 {
            -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
            -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3), 0 0 40px rgba(0, 0, 0, 0.1) inset;
            transition: all .5s ease !important;
        }

        .effect8 {
            box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset !important;
            transition: all .5s ease !important;
            border: 1px solid #f2f2f2 !important;

            overflow: hidden;
        }

        .effect9 {
            box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset !important;
            transition: all .5s ease !important;
            /*border: 1px solid #f2f2f2 !important;*/

            overflow: hidden;
        }

        .item-body:hover {
            transform: translateY(-5px) !important;
            transition: all .5s ease !important;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 13px 27px -5px, rgba(0, 0, 0, 0.3) 0px 8px 16px -8px !important;
        }

        .marg{
            margin-top: 15px; /* Reduced vertical gap */
        }
        
        .dash-icon-wrapper {
            position: absolute;
            right: 13px;
            top: 11px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%; /* Made fully circular */
            padding: 5px;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* Neumorphism Full Design Styles */

        .sales-card {
            border-radius: 12px !important;
            border: none !important;
            box-shadow: 
                5px 5px 15px rgba(163, 177, 198, 0.6), 
                -5px -5px 15px rgba(255, 255, 255, 0.9) !important;
        }

        .tile {
            border-radius: 12px !important;
            border: none !important;
            box-shadow: 6px 6px 12px rgba(163, 177, 198, 0.5), -6px -6px 12px rgba(255, 255, 255, 0.8) !important;
            margin-bottom: 20px;
        }


    </style>
@endpush

@section('content')
    <main class="app-content">


        {{-- 3 STREAMLINED MODERN DASHBOARD CARDS --}}
        <div class="row m-sm-3 mb-3">
            {{-- 1. TODAY'S SALES & PURCHASES --}}
            @if(hasPermission('today.sale.purchase'))
                <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                    <div class="nex-stat-card nex-stat-today">
                        <div class="nex-stat-header">
                            <div class="nex-stat-badge-wrap">
                                <span class="nex-stat-badge">TODAY'S OPERATIONS</span>
                                <span class="nex-live-indicator"><span class="nex-live-dot"></span> Live</span>
                            </div>
                            <div class="nex-stat-icon-wrap">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="nex-stat-body">
                            <div class="nex-stat-row">
                                <div class="nex-metric-col">
                                    <span class="nex-metric-label">Today Sale</span>
                                    <h3 class="nex-metric-value">৳ {{ number_format($todaySaleAmount, 2) }}</h3>
                                </div>
                                <div class="nex-metric-divider"></div>
                                <div class="nex-metric-col">
                                    <span class="nex-metric-label">Today Purchase</span>
                                    <h3 class="nex-metric-value nex-metric-sub">৳ {{ number_format($todayPurchaseAmount, 2) }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="nex-stat-footer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            <span>Compared to today</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. YESTERDAY'S SALES & PURCHASES --}}
            @if(hasPermission('today.sale.purchase'))
                <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                    <div class="nex-stat-card nex-stat-yesterday">
                        <div class="nex-stat-header">
                            <div class="nex-stat-badge-wrap">
                                <span class="nex-stat-badge">YESTERDAY'S SUMMARY</span>
                                <span class="nex-stat-pill-sub">Historical</span>
                            </div>
                            <div class="nex-stat-icon-wrap">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                        </div>

                        <div class="nex-stat-body">
                            <div class="nex-stat-row">
                                <div class="nex-metric-col">
                                    <span class="nex-metric-label">Yesterday Sale</span>
                                    <h3 class="nex-metric-value">৳ {{ number_format($yesterdaySaleAmount, 2) }}</h3>
                                </div>
                                <div class="nex-metric-divider"></div>
                                <div class="nex-metric-col">
                                    <span class="nex-metric-label">Yesterday Purchase</span>
                                    <h3 class="nex-metric-value nex-metric-sub">৳ {{ number_format($yesterdayPurchaseAmount, 2) }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="nex-stat-footer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Compared to yesterday</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 3. AVAILABLE AMOUNT --}}
            @if(hasPermission('available.amount'))
                <div class="col-xl-4 col-lg-4 col-md-6 col-12 mb-3">
                    <div class="nex-stat-card nex-stat-available">
                        <div class="nex-stat-header">
                            <div class="nex-stat-badge-wrap">
                                <span class="nex-stat-badge">AVAILABLE AMOUNT</span>
                                <span class="nex-stat-pill-sub">Liquidity</span>
                            </div>
                            <div class="nex-stat-icon-wrap">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                                    <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                                    <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="nex-stat-body">
                            <div class="nex-stat-single">
                                <span class="nex-metric-label">Current Available Balance</span>
                                <h3 class="nex-metric-value nex-metric-lg">৳ {{ number_format($accounts->sum('total_amount'), 2) }}</h3>
                            </div>
                        </div>

                        <div class="nex-stat-footer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                            <span>Cash & Active Bank Accounts</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- COMMENTED OUT BY USER REQUEST: MONTHLY SALES & PURCHASES
            @if(hasPermission('today.sale.purchase'))
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12 marg">
                    <div class="card overflow-hidden card-item sales-card bg-danger-green">
                        <div class="px-3 pt-3 well item-body pb-2 pt-0">
                            <div><h3 class="mb-3 fs-12 text-fixed-white">MONTHLY</h3></div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Sale: {{ number_format($monthlySaleAmount, 2) }}TK</h5>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Purchase: {{ number_format($monthlyPurchaseAmount, 2) }} TK</h5>
                                    </div>
                                    <span class="float-end my-auto ms-auto">  </span>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-loader dash-icon-wrapper">
                            <h3 class="total-sell-count load-data"></h3>
                            <span class="loader-image" style="font-size: 30px; font-weight: bold;">৳</span>
                        </div>
                        <div id="compositeline2" style="min-height: 30px; text-align: center; margin-top: 10px; margin-bottom: 5px; font-size: 12px;">
                            <p class="mb-0 text-fixed-white op-7">Compared to monthly</p>
                        </div>
                    </div>
                </div>
            @endif
            --}}

            {{-- COMMENTED OUT BY USER REQUEST: TODAY G/A INCOME & EXPENSE
            @if(hasPermission('today.income.expense'))
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12 marg">
                    <div class="card overflow-hidden card-item sales-card bg-danger-yellow">
                        <div class="px-3 pt-3 well item-body pb-2 pt-0">
                            <div><h3 class="mb-3 fs-12 text-fixed-white">TODAY G/A</h3></div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Income: {{ number_format($todayVoucher->where('amount', '>', 0)->sum('amount') + $todayProfitAmount, 2) }} TK</h5>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Expense: {{ number_format($todayVoucher->where('amount', '<', 0)->sum('amount') * (-1), 2) }} TK</h5>
                                    </div>
                                    <span class="float-end my-auto ms-auto">   </span>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-loader dash-icon-wrapper">
                            <h3 class="total-sell-count load-data"></h3>
                            <img class="loader-image" height="35px" width="35px" src="{{ asset('img/icons8-profit-48.png') }}">
                        </div>
                        <div id="compositeline2" style="min-height: 30px; text-align: center; margin-top: 10px; margin-bottom: 5px; font-size: 12px;">
                            <p class="mb-0 text-fixed-white op-7">Compared to today</p>
                        </div>
                    </div>
                </div>
            @endif
            --}}
        </div>

        {{-- COMMENTED OUT BY USER REQUEST: ROW 2 DASHBOARD CARDS (MONTHLY, LIABILITIES, SMS INFO, PREVIOUS AVAILABLE AMOUNT)
        <div class="row m-sm-3 mt-3 " style="color: white">
            @if(hasPermission('monthly.income.expense'))
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12 marg">
                    <div class="card overflow-hidden sales-card bg-danger-gradient">
                        <div class="px-3 pt-3 pb-2 pt-0">
                            <div><h3 class="mb-3 fs-12 text-fixed-white">MONTHLY</h3></div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Income: {{ number_format($monthlyVoucher->where('amount', '>', 0)->sum('amount') + $monthlyProfitAmount, 2) }} TK</h5>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Expense: {{ number_format($monthlyVoucher->where('amount', '<', 0)->sum('amount') * (-1), 2) }} TK</h5>
                                    </div>
                                    <span class="float-end my-auto ms-auto"> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if(hasPermission('accounts.liabilities'))
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12 marg">
                    <div class="card overflow-hidden sales-card bg-danger-red">
                        <div class="px-3 pt-3 pb-2 pt-0">
                            <div><h3 class="mb-3 fs-12 text-fixed-white">LIABILITIES</h3></div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Payable Due: {{ number_format($payable, 2) }} TK</h5>
                                        <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Receivable Due: {{ number_format($receivable, 2) }} TK</h5>
                                    </div>
                                    <span class="float-end my-auto ms-auto"> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @php
                $showSmsInfo = \App\SoftwareSetting::companies()->where('title', 'Show SMS Info In Dashboard')->first();
            @endphp
            <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12 marg">
                <div class="card overflow-hidden sales-card bg-danger-green">
                    <div class="px-3 pt-3 pb-2 pt-0">
                        <div><h3 class="mb-3 fs-12 text-fixed-white">SMS INFO</h3></div>
                        <div class="pb-0 mt-0">
                            <div class="d-flex">
                                <div>
                                    <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Balance: 00 TK</h5>
                                    <h5 class="fs-20 fw-bold mb-1 text-fixed-white">Credit Limit: 00 TK</h5>
                                </div>
                                <span class="float-end my-auto ms-auto"> </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(hasPermission('available.amount'))
                <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12 marg">
                    <div class="card overflow-hidden sales-card bg-danger-yellow">
                        <div class="px-3 pt-3 pb-2 pt-0">
                            <div><h3 class="mb-3 fs-12 text-fixed-white"> Available Amount</h3></div>
                            <div class="pb-0 mt-0">
                                <div class="d-flex">
                                    <div><h4 class="fs-20 fw-bold mb-1 text-fixed-white">Balance :{{ number_format($accounts->sum('total_amount'), 2) }}</h4>
                                        <p class="mb-0 fs-12 text-fixed-white op-7">Compared to last Time</p>
                                    </div>
                                    <span class="float-end my-auto ms-auto"> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        --}}

        <br>




    <div>
{{--        <div class="row">--}}
{{--            @if(hasPermission('today.sale.purchase'))--}}
{{--                <div class="col-md-6 col-lg-3">--}}
{{--                    <div class="widget-small primary coloured-icon">--}}
{{--                        <i class="icon fa fa-shopping-cart fa-2x"></i>--}}
{{--                        <div class="info" style="padding-top: 5px !important; padding-left: 5px !important;">--}}
{{--                            <h4 style="margin-top: 0 !important; padding-top: 0!important;">Yesterday</h4>--}}
{{--                            <p style="padding-top: 0 !important; font-size: 13px !important;"><b>Sale: {{ number_format($yesterdaySaleAmount, 2) }} TK</b></p>--}}
{{--                            <p style="padding-top: 0 !important; margin-top: -5px !important; font-size: 13px !important;"><b>Purchase: {{ number_format($yesterdayPurchaseAmount, 2) }} TK</b></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}

{{--            @if(hasPermission('today.sale.purchase'))--}}
{{--                <div class="col-md-6 col-lg-3">--}}
{{--                    <div class="widget-small primary coloured-icon">--}}
{{--                        <i class="icon fa fa-shopping-cart fa-2x" style="background: #90a229"></i>--}}
{{--                        <div class="info" style="padding-top: 5px !important; padding-left: 5px !important;">--}}
{{--                            <h4 style="margin-top: 0 !important; padding-top: 0!important;">Today</h4>--}}
{{--                            <p style="padding-top: 0 !important; font-size: 13px !important;"><b>Sale: {{ number_format($todaySaleAmount, 2) }} TK</b></p>--}}
{{--                            <p style="padding-top: 0 !important; margin-top: -5px !important; font-size: 13px !important;"><b>Purchase: {{ number_format($todayPurchaseAmount, 2) }} TK</b></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}

{{--            @if(hasPermission('today.income.expense'))--}}
{{--                <div class="col-md-6 col-lg-3">--}}
{{--                    <div class="widget-small info coloured-icon">--}}
{{--                        <i class="icon fa fa-shopping-cart fa-2x"></i>--}}
{{--                        <div class="info" style="padding-top: 5px !important; padding-left: 5px !important;">--}}
{{--                            <h4 style="margin-top: 0 !important; padding-top: 0!important;">Today</h4>--}}
{{--                            <p style="padding-top: 0 !important; font-size: 13px !important;"><b>Income: {{ number_format($todayVoucher->where('amount', '>', 0)->sum('amount'), 2) }} TK</b></p>--}}
{{--                            <p style="padding-top: 0 !important; margin-top: -5px !important; font-size: 13px !important;"><b>Expense:{{ number_format($todayVoucher->where('amount', '<', 0)->sum('amount') * (-1), 2) }} TK</b></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}


{{--            @if(hasPermission('monthly.sale.purchase'))--}}
{{--                <div class="col-md-6 col-lg-3">--}}
{{--                    <div class="widget-small primary coloured-icon">--}}
{{--                        <i class="icon fa fa-shopping-cart fa-2x" style="background: #5ca9a9"></i>--}}
{{--                        <div class="info" style="padding-top: 5px !important; padding-left: 5px !important;">--}}
{{--                            <h4 style="margin-top: 0 !important; padding-top: 0!important;">Monthly</h4>--}}
{{--                            <p style="padding-top: 0 !important; font-size: 13px !important;"><b>Sale: {{ number_format($monthlySaleAmount, 2) }} TK</b></p>--}}
{{--                            <p style="padding-top: 0 !important; margin-top: -5px !important; font-size: 13px !important;"><b>Purchase: {{ number_format($monthlyPurchaseAmount, 2) }} TK</b></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}

{{--            @if(hasPermission('monthly.income.expense'))--}}
{{--                <div class="col-md-6 col-lg-3">--}}
{{--                    <div class="widget-small info coloured-icon">--}}
{{--                        <i class="icon fa fa-shopping-cart fa-2x" style="background: #82609c"></i>--}}
{{--                        <div class="info" style="padding-top: 5px !important; padding-left: 5px !important;">--}}
{{--                            <h4 style="margin-top: 0 !important; padding-top: 0!important;">Monthly</h4>--}}
{{--                            <p style="padding-top: 0 !important; font-size: 13px !important;"><b>Income: {{ number_format($monthlyVoucher->where('amount', '>', 0)->sum('amount'), 2) }} TK</b></p>--}}
{{--                            <p style="padding-top: 0 !important; margin-top: -5px !important; font-size: 13px !important;"><b>Expense: {{ number_format($monthlyVoucher->where('amount', '<', 0)->sum('amount') * (-1), 2) }} TK</b></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}

{{--            @if(hasPermission('accounts.liabilities'))--}}
{{--                <div class="col-md-6 col-lg-3">--}}
{{--                    <div class="widget-small danger coloured-icon">--}}
{{--                        <i class="icon fa fa-briefcase fa-2x"></i>--}}
{{--                        <div class="info" style="padding-top: 5px !important; padding-left: 5px !important;">--}}
{{--                            <h4 style="margin-top: 0 !important; padding-top: 0!important;">Liabilities</h4>--}}
{{--                            <p style="padding-top: 0 !important; font-size: 13px !important;"><b>Payable Due: {{ number_format($payable, 2) }} TK</b></p>--}}
{{--                            <p style="padding-top: 0 !important; margin-top: -5px !important; font-size: 13px !important;"><b>Receivable Due: {{ number_format($receivable, 2) }} TK</b></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--        </div>--}}
    </div>





        <div class="row ">
            <!-- Available Amount -->
            @if(hasPermission('available.amount'))
                <div class="col-sm-12 px-4">
                    <div class="tile ">
                        <table class="table table-bordered table-sm text-center">
                            <tr>
                                <th colspan="2" class="dash-head">
                                    <h4 class="pull-left">Available Amount</h4>
                                    <h4 class="pull-right">{{ number_format($accounts->sum('total_amount'), 2) }} TK</h4>
                                </th>
                            </tr>

                            @foreach($accounts as $account)
                                <tr>
                                    <td width="50%" class="text-left">
                                        <b>{{ $account->account_name }}</b>
                                    </td>
                                    <td width="50%" class="text-right">
                                        <b>{{ number_format((float) ($account->total_amount ?? 0), 2) }}</b> &nbsp;TK
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endif

            <!-- Company Liabilities in Dashboard -->
            @php
                $isLiabilityDashboardEnabled = isset($showCompanyLiabilityInDashboard)
                    ? $showCompanyLiabilityInDashboard
                    : (optional($settings->where('title', 'Show Company Liability In Dashboard')->first())->options == 'yes');
            @endphp
            @if($isLiabilityDashboardEnabled)
                @php
                    $liabilitiesList = isset($dashboardLiabilities)
                        ? $dashboardLiabilities
                        : \App\Liability::where('company_id', companyId())->latest()->get();
                @endphp
                <div class="col-sm-12 px-4">
                    <div class="tile">
                        <table class="table table-bordered table-sm text-center">
                            <tr>
                                <th colspan="2" class="dash-head">
                                    <h4 class="pull-left"><i class="fa fa-th-list"></i> Company Liabilities</h4>
                                    <h4 class="pull-right">Current Balance: {{ number_format($liabilitiesList->sum('current_balance'), 2) }} TK</h4>
                                </th>
                            </tr>

                            @forelse($liabilitiesList as $liability)
                                <tr>
                                    <td width="50%" class="text-left">
                                        <b>{{ $liability->name }}</b>
                                    </td>
                                    <td width="50%" class="text-right text-danger">
                                        <b>{{ number_format($liability->current_balance, 2) }}</b> &nbsp;TK
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No liability records found</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            @endif

            <!-- Monthly Account Chart -->
            @if(hasPermission('monthly.account.chart'))
                <div class="col-sm-12 px-4">
                    <div class="tile" style="max-height: 550px">
                        <h3 class="tile-title">Monthly Account Chart</h3>
                        <div class="tile-body">
                            <div class="embed-responsive embed-responsive-16by9" style="height: 254px">
                                <canvas class="embed-responsive-item" id="lineChartDemo" style="height: 250px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>



        @php
            $SMSAPI = App\SmsApi::where('status',1)->first();
        @endphp

        <input type="hidden" name="brToken" value="{{$SMSAPI->api_url ?? 0}}">
    </main>



    <script src="{{ asset('assets/admin/js/jquery-3.2.1.min.js') }}"></script>
{{--    <script src="{{ asset('assets/admin/js/plugins/pace.min.js') }}"></script>--}}
    <script type="text/javascript" src="{{ asset('assets/admin/js/plugins/chart.js') }}"></script>
    <script type="text/javascript">
        var days = [<?php echo '"'.implode('","', $week_days).'"' ?>];
        var sales = [<?php echo '"'.implode('","', $sales_graph).'"' ?>];
        var purchases = [<?php echo '"'.implode('","', $purchase_graph).'"' ?>];

        var data = {
            labels: days,
            datasets: [
                {
                    label: "My First dataset",
                    fillColor: "rgba(220,220,220,0.2)",
                    strokeColor: "rgba(220,220,220,1)",
                    pointColor: "rgba(220,220,220,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(220,220,220,1)",
                    data: purchases
                },
                {
                    label: "My Second dataset",
                    fillColor: "rgba(151,187,205,0.2)",
                    strokeColor: "rgba(151,187,205,1)",
                    pointColor: "rgba(151,187,205,1)",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "rgba(151,187,205,1)",
                    data: sales
                }
            ]
        };
        var pdata = [
            {
                value: 300,
                color:"#F7464A",
                highlight: "#FF5A5E",
                label: "Red"
            },
            {
                value: 50,
                color: "#46BFBD",
                highlight: "#5AD3D1",
                label: "Green"
            },
            {
                value: 100,
                color: "#FDB45C",
                highlight: "#FFC870",
                label: "Yellow"
            }
        ]

        var ctxl = $("#lineChartDemo").get(0).getContext("2d");
        var lineChart = new Chart(ctxl).Line(data);




        function getAvailableSmartBalance() {
            $('.available-balance').text('Balance: Unlimited');
        }
        getAvailableSmartBalance();

    </script>
{{--    <!-- Google analytics script-->--}}
{{--    <script type="text/javascript">--}}
{{--        if(document.location.hostname == 'pratikborsadiya.in') {--}}
{{--            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){--}}
{{--                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),--}}
{{--                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)--}}
{{--            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');--}}
{{--            ga('create', 'UA-72504830-1', 'auto');--}}
{{--            ga('send', 'pageview');--}}
{{--        }--}}
{{--    </script>--}}
@endsection
