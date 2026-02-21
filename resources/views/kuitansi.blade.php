@php
$logo = base64_encode(file_get_contents(public_path('images/nusaqu.png')));
$logoFarm = base64_encode(file_get_contents(public_path('images/nusafarm.png')));
$logoTtd = base64_encode(file_get_contents(public_path('images/ttd.png')));
@endphp


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
    @page {
        size: A4;
        margin: 20mm;
    }

    body {
        font-family: "Times New Roman", serif;
        font-size: 14px;
        color: #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .text-left {
        text-align: left;
    }

    .header-table td {
        vertical-align: middle;
    }

    .logo-left img {
        width: 80%;
    }

    .logo-right img {
        width: 60%;
    }

    .company-title {
        color: orange;
        font-size: 22px;
        font-weight: bold;
    }

    .company-address {
        font-size: 11px;
    }

    .divider {
        border-top: 2px solid orange;
        margin: 15px 0;
    }

    .receipt-title {
        background: orange;
        color: white;
        padding: 8px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
    }

    .detail-table td {
        padding: 6px 0;
    }

    .detail-label {
        width: 25%;
    }

    .detail-separator {
        width: 5%;
        text-align: center;
    }

    .detail-value {
        width: 70%;
    }

    .line {
        border-top: 1px solid lightgray;
        margin: 8px 0;
    }

    .note {
        font-size: 13px;
    }

    .signature img {
        width: 25%;
    }
    </style>
</head>

<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-left text-right" width="25%">
                <img src="data:image/png;base64,{{ $logo }}">
            </td>

            <td class="text-center" width="50%">
                <div class="company-title">nusaQu Indonesia</div>
                <div class="company-address">
                    Office : Jalan Kandang Sapi, RT.02/RW.08, Sasak Panjang,<br>
                    Kec. Tajur Halang, Kabupaten Bogor, Jawa Barat 16320
                </div>
            </td>

            <td class="logo-right text-left" width="25%">
                <img src="data:image/png;base64,{{ $logoFarm }}">
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- INFO -->
    <table>
        <tr>
            <td class="text-left">
                <span style="font-size:12px;">Sudah Diterima Dari</span>
                <h3 style="margin:5px 0;">{{ $invest->users->nama_users }}</h3>
            </td>

            <td class="text-right">
                <h3 style="margin:0;">KUITANSI</h3>
                <span style="font-size:12px;">
                    No Kuitansi : {{ $invest->kode_investasi }}
                </span>
                <h5 style="margin:5px 0;">
                    {{ $invest->tanggal_investasi->format('d F Y') }}
                </h5>
            </td>
        </tr>
    </table>

    <!-- DETAIL TITLE -->
    <div style="margin-top:15px;">
        <div class="receipt-title">Detail Kuitansi</div>
    </div>

    <!-- DETAIL -->
    <table class="detail-table" style="margin-top:15px;">
        <tr>
            <td class="detail-label"><strong>Banyaknya Uang</strong></td>
            <td class="detail-separator">:</td>
            <td class="detail-value">{{ $invest->jumlah_inves_terbilang }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table class="detail-table">
        <tr>
            <td class="detail-label"><strong>Untuk Pembayaran</strong></td>
            <td class="detail-separator">:</td>
            <td class="detail-value">{{ $invest->description }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table class="detail-table">
        <tr>
            <td class="detail-label"><strong>Jumlah</strong></td>
            <td class="detail-separator">:</td>
            <td class="detail-value">
                Rp {{ number_format($invest->jumlah_inves, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- NOTE -->
    <div style="margin-top:15px;">
        <strong>Catatan :</strong><br>
        <span class="note">
            Kuitansi ini merupakan bukti pembelian dan pembayaran yang sah
        </span>
    </div>

    <!-- SIGNATURE -->
    <div class="signature text-right" style="margin-top:30px;">
        <h3>
            Bogor, {{ $invest->tanggal_investasi->format('d F Y') }}
        </h3>
        <img src="data:image/png;base64,{{ $logoTtd }}"><br>
        <span>( Nanang Sugiarto )</span>
    </div>

</body>

</html>