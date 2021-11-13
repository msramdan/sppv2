<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel='icon' href='{{ public_path('/storage/') . \Setting::getSetting()->favicon }}' type='image/x-icon' />
    {{-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous"> --}}
    <title>Laporan Tagihan</title>
    <style>
        body {
            padding: 0;
            margin: 0;
        }

        .page {
            max-width: 80em;
            /* margin: 0 auto;' */
            /* position: absolute; */
            /* top: 170px; */
            position: relative;
            top: 15;
        }

        table th,
        table td {
            text-align: left;
        }

        table.layout {
            width: 100%;
            border-collapse: collapse;
        }

        table.display {
            margin: 1em 0;
        }

        table.display th,
        table.display td {
            border: 1px solid #B3BFAA;
            padding: .5em 1em;
        }

        table.display th {
            background: #D5E0CC;
        }

        table.display td {
            background: #fff;
        }

        /* table.responsive-table{
            box-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
        }  */

        .customer {
            padding-left: 600px;
        }

        .logo {
            position: absolute;
            left: 150px;
            top: 20px;
            z-index: 999;
        }

        .koplaporan {
            position: relative;
            height: 120px;
        }

        .logo img {
            width: 120px;
            height: 120px;
            /* position: absolute; */

        }

        .judul {
            position: absolute;
            top: 0;
            text-align: center;
        }

        .garis {
            margin-top: 160px;
            height: 3px;
            border-top: 3px solid black;
            border-bottom: 1px solid black;
        }

        .info {
            position: relative;
            top: 45px;
            font-size: 20px;
            text-align: center;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        /* .header{
            position: relative;
            top: 0px;
            font-size: 20px;
            text-align: right;
        } */
        .sub-header {
            font-size: 20px;
        }

    </style>
</head>

<body>
    <div class="header">
        <div class="koplaporan">
            <div class="logo">
                <img src="{{ public_path('/img/') . \Setting::getSetting()->logo }}" alt="">
            </div>
            <div class="judul">
                <p>
                    {{ $sekolahInfo->nama_sekolah }}<br>
                    <span class="sub-header">{{ $sekolahInfo->alamat }}</span><br>
                    <span class="sub-header">{{ $sekolahInfo->kota }}</span><br>
                    <span class="sub-header">Telp. {{ $sekolahInfo->no_telp }}</span><br>

                </p>
            </div>
            <div class="garis"></div>
        </div>

        {{-- <h5>Dicetak Tanggal : {{ date("d-m-Y") }}</h5> --}}
        {{-- <br> --}}

        {{-- <p><small style="opacity: 0.5;">{{ $penjualan->created_at->format('d-m-Y H:i:s') }}</small></p> --}}
    </div>

    <div class="info">
        <strong>Laporan Tagihan </strong> <br>
        <table style="margin-left: auto; margin-right: auto;">
            <tr>
                <td style="vertical-align: text-top">Jenis Pembayaran</td>
                <td style="padding-left: 10px; padding-right: 10px; vertical-align: text-top">:</td>
                <td>{{ $data->first()->jenis_pembayaran->nama_pembayaran }}</td>
            </tr>
            <tr>
                <td>Tahun Pelajaran</td>
                <td style="padding-left: 10px; padding-right: 10px ">:</td>
                <td>{{ $data->first()->jenis_pembayaran->tahunajaran->tahun_ajaran }}</td>
            </tr>
            <tr>
                <td>Nominal</td>
                <td style="padding-left: 10px; padding-right: 10px ">:</td>
                <td>Rp. {{ number_format($data->first()->jenis_pembayaran->harga) }}</td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td style="padding-left: 10px; padding-right: 10px ">:</td>
                <td>{{ $jenisPembayaranTipe === 'bulanan' ? 'Bulanan' : 'Angsuran/Bebas' }}</td>
            </tr>
            <tr>
                <td>Dicetak Tanggal</td>
                <td style="padding-left: 10px; padding-right: 10px ">:</td>
                <td>{{ date('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td style="padding-left: 10px; padding-right: 10px ">:</td>
                <td>{{ $namaKelas == '' ? 'Semua Kelas' : $namaKelas }}</td>
            </tr>
        </table>

        <table>
            <tr>
                <td>Keterangan :</td>
            </tr>
            <tr>
                <td><span style="color: green">v</span> = Lunas |</td>
                <td><span style="color: red">x</span> = Belum Lunas</td>
            </tr>
        </table>
    </div>
    <div class="page">
        {{-- @if ($mode === 'simple') --}}

        <table class="layout display responsive-table" style="font-size: 18px">
            <thead>
                <tr>
                    <th style="text-align: center">#</th>
                    <th style="text-align: center">NIS/Nama</th>

                    @if ($jenisPembayaranTipe === 'bulanan')
                        @foreach ($bulan as $item)
                            <th>{{ $item }}</th>
                        @endforeach
                        <th>Total</th>
                        <th>Sisa</th>
                    @else
                        <th>Status</th>
                        <th>Sisa</th>
                        <th>Total Bayar</th>
                    @endif

                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotal = 0;
                    $totalBayar = 0;
                    $totalSisa = 0;
                    $sisa = 0;

                    $totalBulanJanuari = 0;
                    $totalBulanFebruari = 0;
                    $totalBulanMaret = 0;
                    $totalBulanApril = 0;
                    $totalBulanMei = 0;
                    $totalBulanJuni = 0;
                    $totalBulanJuli = 0;
                    $totalBulanAgustus = 0;
                    $totalBulanSeptember = 0;
                    $totalBulanOktober = 0;
                    $totalBulanNovember = 0;
                    $totalBulanDesember = 0;

                    $sisaBulanJanuari = 0;
                    $sisaBulanFebruari = 0;
                    $sisaBulanMaret = 0;
                    $sisaBulanApril = 0;
                    $sisaBulanMei = 0;
                    $sisaBulanJuni = 0;
                    $sisaBulanJuli = 0;
                    $sisaBulanAgustus = 0;
                    $sisaBulanSeptember = 0;
                    $sisaBulanOktober = 0;
                    $sisaBulanNovember = 0;
                    $sisaBulanDesember = 0;
                @endphp
                @foreach ($data as $row)
                    <tr>
                        <td width="10px">{{ $loop->iteration }}</td>
                        <td width="80">
                            {{ @$row->siswa->nis }} -
                            {{ @$row->siswa->kelas->nama_kelas }} <br>
                            {{ @$row->siswa->nama_lengkap }}
                        </td>

                        @php
                            $total = 0;
                        @endphp

                        @foreach ($row->tagihan_detail as $index => $item)
                            <td style="width: 60px; text-align:center;">
                                @if ($item->status === 'Lunas')
                                    <span style="color: green">v</span>
                                    @php
                                        $total += $data->first()->jenis_pembayaran->harga;
                                    @endphp
                                @endif

                                @php
                                    foreach ($bulanLengkap as $value) {
                                        // jika nama bulan sama dengan daftar nama bulan
                                        if ($value == $item->keterangan) {
                                            ${'totalBulan' . $value} += $item->total_bayar;
                                            ${'sisaBulan' . $value} += $item->sisa;
                                        }
                                    }
                                @endphp

                                @if ($item->status === 'Belum Lunas')
                                    <span style="color: red">x</span>
                                    {{-- <i class="fas fa-times-circle text-danger"></i> --}}
                                    <small style="margin-bottom: 0">Dibayar:
                                        Rp. {{ number_format($item->total_bayar) }}</small>
                                    <br>
                                    <small style="margin-top: 0">Sisa:
                                        Rp. {{ number_format($item->sisa) }}</small>
                                    @php
                                        $sisa += $item->sisa;
                                        $total += $item->total_bayar;
                                    @endphp
                                @endif
                            </td>
                        @endforeach

                        {{-- <td>Total BLN:</td> --}}

                        @if ($jenisPembayaranTipe !== 'bulanan')
                            <td style="width: 100px; text-align:right;">
                                1 Rp. {{ number_format($row->tagihan_detail[0]->sisa) }}
                            </td>

                            <td style="width: 100px; text-align:right;">
                                2 Rp. {{ number_format($row->tagihan_detail[0]->total_bayar) }}

                                @if ($row->tagihan_detail[0]->total_bayar != 0)
                                    @php
                                        $totalBayar = $totalBayar + $row->tagihan_detail[0]->total_bayar;
                                    @endphp
                                @endif
                            </td>
                        @endif

                        @php
                            $grandTotal += $total;
                            $totalSisa += $sisa;
                        @endphp

                        @if ($jenisPembayaranTipe === 'bulanan')
                            <td style="width: 20px; text-align:right; margin:0;">Rp. {{ number_format($total) }}</td>
                            <td style="width: 20px; text-align:right; margin:0;">Rp. {{ number_format($sisa) }}
                                @php
                                    $sisa = 0;
                                @endphp
                            </td>
                        @endif
                    </tr>
                @endforeach

                @if ($jenisPembayaranTipe === 'bulanan')
                    <tr>
                        <td colspan="2" style="text-align:center;">
                            <span style="font-weight: bold">Total</span>
                        </td>


                        @foreach ($bulanLengkap as $index => $item)

                            <td style="text-align:center;">
                                <small>Dibayar: Rp. {{ number_format(${'totalBulan' . $item}) }}</small>
                                <br>
                                <small>Sisa: Rp. {{ number_format(${'sisaBulan' . $item}) }}</small>
                            </td>
                        @endforeach

                        <td style="text-align:right;">Rp. {{ number_format($grandTotal) }}
                        </td>
                        <td style="text-align:right;">Rp. {{ number_format($totalSisa) }}
                        </td>
                    </tr>

                    {{-- <tr>
                        <td colspan="13" style="text-align:right;">
                        </td>
                        <td colspan="1" style="text-align:right;">Grand Total</td>
                        <td style="text-align:right;">Rp. {{ number_format($grandTotal) }}
                        </td>
                        <td style="text-align:right;">Rp. {{ number_format($totalSisa) }}
                        </td>
                    </tr> --}}

                    {{-- <tr>
                        <td colspan="12" style="text-align:right;"></td>
                        <td colspan="2" style="text-align:right;">Sisa</td>
                        <td style="text-align:right;">{{ number_format($sisa) }}</td>
                    </tr> --}}
                @else
                    <tr>
                        {{-- <td colspan="14"style="text-align:right; border: 1px solid black;"></td> --}}
                        <td colspan="3"></td>
                        <td colspan="1" style="text-align:right;">Grand Total</td>
                        <td style="text-align:right;">Rp. {{ number_format($totalBayar) }}</td>
                    </tr>

                @endif
            </tbody>
        </table>

        <table border="0" style="width: 100%; font-size: 20px">
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: center">{{ $sekolahInfo->kota }}, {{ date('d-m-Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: center">Kepala Sekolah 3</td>
                <td></td>
                <td style="text-align: center">Bendahara</td>
            </tr>
            <tr>
                <td style="width: 100px"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: center">_________________</td>
                <td style="height: 200px"></td>
                <td style="text-align: center">_________________</td>
            </tr>
        </table>
    </div>
</body>

</html>
