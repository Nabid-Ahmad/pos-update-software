@extends('admin.master')
@section('title', 'Barcode Print')
{{--@dd('sdfkjf')--}}
@section('content')
    <style>
        @media print {
            .page-break {
                display: block;
                page-break-after: always;
                page-break-before: always;
            }
            .d-print-block {
                display: block;
            }
            /* Hide unnecessary elements during normal A4 print */
            .app-title, .app-header, .app-sidebar, .d-print-none {
                display: none !important;
            }
            .app-content {
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
            }
            .tile {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .tile-body {
                min-height: auto !important;
            }
            /* Force 4 columns per row in print mode for col-md-3 */
            .col-md-3 {
                flex: 0 0 25% !important;
                max-width: 25% !important;
            }
        }
        .d-print-block {
            display: none;
        }
    </style>

    <main class="app-content">
        <!-- Breadcrumb -->
        <div class="app-title">
            <div>
                <h1><i class="fa fa-file-text-o"></i> Barcode Print</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="#"> Barcode Print</a></li>
            </ul>
        </div>

        <div class="row multiple-print">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body" style="min-height: 640px !important;">
                        <section class="invoice">
                            <div class="row d-print-none mt-2">

                                <div class="col-12 float-right">
                                    <div class="btn-group float-left ml-5">
                                        <strong style="margin-right: 10px; margin-top: 3px">Print Size: </strong>

                                        <strong style="margin-right: 10px; margin-top: 3px">Print Size: </strong>
                                        <span class="mt-1 font-weight-bold text-primary" style="font-size: 16px;">38mm X 25mm</span>
                                        <input type="hidden" name="optradio" value="custom-size">
                                    </div>
                                    <div class="btn-group float-right">

                                        @if(request('type') == 'normal')
                                            <button class="btn btn-primary" type="button" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                                        @endif
                                        <button class="btn btn-success" type="button" onclick="singlePrint()"><i class="fa fa-print"></i>Label Print</button>
                                        <a class="btn btn-secondary" href="{{ route('barcodes.index') }}"><i class="fa fa-backward"></i> Back To List</a>
                                    </div>
                                </div>
                            </div>

                            @if(request('type') == 'normal')
                                <br>
                                <br>

                                <div class="row ">
                                    <div class="col-md-12">
                                        <div class="row">
                                            @for ($i = 1; $i <= 40; $i++)
                                                <div class="col-md-3 text-center mb-5">
                                                    <div class="text-center">
                                                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" style="width: 160px; height: 60px;" />
                                                    </div>
                                                    <b style="font-size: 15px;">{{ $barcode->barcode_number }}</b>
                                                    <br>
                                                    <b> {{ $barcode->product->product_name }}</b>
                                                </div>

                                                @if ($i == 40)
                                                    <div class="page-break"></div>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            @else
                                <br>
                                <br>

                                {{--                            <div class="row">--}}
                                {{--                                <div class="col-md-3 text-center mb-5 d-print-none">--}}
                                {{--                                    <div class="text-center">--}}
                                {{--                                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" style="width: 160px; height: 60px;" />--}}
                                {{--                                    </div>--}}
                                {{--                                    <b>{{ $barcode->barcode_number }}</b>--}}
                                {{--                                    <br>--}}
                                {{--                                    <b> {{ $barcode->product->product_name }}</b>--}}
                                {{--                                </div>--}}
                                {{--                            </div>--}}

                                <div class="row" id="DivIdToPrint">
                                    <div class="col-md-12">

                                        <!-- // 38mm x 25mm Barcode Size -->
                                        <div class="row custom-size per-page-1">
                                            <div style="width: 38mm !important; height: 25mm !important; padding: 1mm !important; text-align: center; box-sizing: border-box; overflow: hidden; margin: 0; font-family: Arial, sans-serif;">
                                                <p style="text-align: center !important; font-size: 10px; margin: 0 !important; font-weight: bold; line-height: 1.2;">{{ Str::limit($barcode->product->product_name, 25) }}</p>
                                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" style="width: 34mm !important; height: 10mm !important; margin-top: 2px; margin-bottom: 2px;" />
                                                <p style="text-align: center !important; font-size: 10px; margin: 0 !important; line-height: 1.2;"><b>{{ $barcode->barcode_number }}</b></p>
                                            </div>
                                        </div>

                                        {{--                                    <div class="row da-print-block per-page-4">--}}
                                        {{--                                        <div style="width: 220px !important; margin-top: -5px !important;">--}}
                                        {{--                                            <table>--}}
                                        {{--                                                <tr style="margin-top: -5px !important">--}}
                                        {{--                                                    <td style="margin-left: 3px !important; margin-right: 5px !important">--}}
                                        {{--                                                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" style="width: 100px !important; height: 13px !important;" />--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: 2px">{{ $barcode->barcode_number }}</p>--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: -5px !important;"> {{ $barcode->product->product_name }}</p>--}}
                                        {{--                                                    </td>--}}
                                        {{--                                                    <td>&nbsp;</td>--}}
                                        {{--                                                    <td style="margin-left: 3px">--}}
                                        {{--                                                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" style="width: 100px !important; height: 13px !important;" />--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: 2px">{{ $barcode->barcode_number }}</p>--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: -5px !important;"> {{ $barcode->product->product_name }}</p>--}}
                                        {{--                                                    </td>--}}
                                        {{--                                                </tr>--}}
                                        {{--                                                    <td style="margin-left: 3px">--}}
                                        {{--                                                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" style="width: 100px !important; height: 13px !important;" />--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: 2px">{{ $barcode->barcode_number }}</p>--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: -5px !important;"> {{ $barcode->product->product_name }}</p>--}}
                                        {{--                                                    </td>--}}
                                        {{--                                                    <td></td>--}}
                                        {{--                                                    <td style="margin-left: 3px">--}}
                                        {{--                                                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" style="width: 100px !important; height: 13px !important;" />--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: 2px">{{ $barcode->barcode_number }}</p>--}}
                                        {{--                                                        <p style="text-align: center; font-size: 5px; margin-top: -5px !important;"> {{ $barcode->product->product_name }}</p>--}}
                                        {{--                                                    </td>--}}
                                        {{--                                                </tr>--}}
                                        {{--                                            </table>--}}
                                        {{--                                        </div>--}}
                                    </div>
                                </div>
                            @endif
                        </section>
                    </div>
                </div>
            </div>
        </div>

        @if(request('type') == 'normal')
            <div class="row d-none" id="DivIdToPrint">
                <div class="col-md-12">
                    <div class="tile">
                        <div class="tile-body">
                            <section class="invoice">
                                <div class="row ">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-3 text-center mb-5 mx-auto" style="text-align: center !important;">
                                                <div class="text-center">
                                                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, "C128") }}" alt="barcode" style="width: 160px; height: 60px;" />
                                                </div>
                                                <b>{{ $barcode->barcode_number }}</b>
                                                <br>
                                                <b> {{ $barcode->product->product_name }}</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>
@endsection

@section('js')
    <script type="text/javascript">
        // window.print();

        function singlePrint() {
            var divToPrint=document.getElementById('DivIdToPrint');
            var newWin=window.open('','Print-Window');
            newWin.document.open();
            newWin.document.write('<html><head><style>@page { size: 38mm 25mm; margin: 0; } body { margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; } .row { margin: 0; padding: 0; }</style></head><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');

            newWin.document.close();

            setTimeout(function(){newWin.close();},10);
        }
    </script>
@stop
