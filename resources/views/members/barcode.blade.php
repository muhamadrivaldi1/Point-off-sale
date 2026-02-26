<!DOCTYPE html>
<html>
<head>
    <title>Cetak Kartu Member</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .card {
            width: 350px;
            border: 2px solid #000;
            padding: 20px;
            margin: auto;
            text-align: center;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .name {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .barcode-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .barcode-text {
            margin-top: 10px;
            font-size: 16px;
            letter-spacing: 3px;
        }
    </style>
</head>
<body onload="window.print()">

<div class="card">

    <div class="title">
        KARTU MEMBER
    </div>

    <div class="name">
        {{ $member->name }}
    </div>

    <div class="barcode-wrapper">
        {!! DNS1D::getBarcodeHTML($member->barcode, 'C128', 2, 60) !!}
    </div>

    <div class="barcode-text">
        {{ $member->barcode }}
    </div>

</div>

</body>
</html>