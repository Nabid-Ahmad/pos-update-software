<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Barcode Print</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=BIZ+UDPMincho&display=swap');

        * {
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            font-family: 'BIZ UDPMincho', serif !important;
        }

        @media print {
            @page {
                size: 35mm 23mm; /* Each label size */
                margin: 0;
            }

            body {
                margin: 0;
            }

            .page-break {
                display: block;
                page-break-after: always;
                width: 40mm;
                height: 20mm;
                text-align: center;
                font-size: 10px;
            }

            img.barcode--product_image {
                width: 95% !important;
                height: auto !important;
            }
        }

        body {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: flex-start;
        }

        .page-break {
            width: 40mm;
            height: 20mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px solid #eee;
            margin: 2px;
        }

        .barcode--product_name {
            font-size: 10px;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .barcode--product_price {
            font-size: 9px;
            margin-top: 1px;
        }
    </style>
</head>
<body>

    @for ($i = 1; $i <= 3; $i++)
        <div class="page-break">
            <img class="barcode--product_image"
                src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode->barcode_number, 'C128') }}" alt="barcode">

            <div class="barcode--product_name">{{ $barcode->product->product_name }}</div>
            <div class="barcode--product_price">{{ $barcode->barcode_number }}</div>
        </div>
    @endfor

    <script>
        window.onload = function() {
            setTimeout(function(){
                window.print();
            }, 500);
        }
    </script>

</body>
</html>
