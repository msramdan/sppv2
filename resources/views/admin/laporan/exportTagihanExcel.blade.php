<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Data Produk</title>

</head>

<body>
    <div class="header">
        <h4>{{ $sekolahInfo->nama_sekolah }}</h4>
        <h4>{{ $sekolahInfo->alamat }} {{ $sekolahInfo->kota }}</h4>
        <h4>No. Telpon : {{ $sekolahInfo->no_telp }}</h4>
        {{-- <h4 style="line-height: 0px;">Invoice: #{{ $penjualan->invoice }}</h4>
        <p><small style="opacity: 0.5;">{{ $penjualan->created_at->format('d-m-Y H:i:s') }}</small></p> --}}
    </div>
    <p></p>
    @if (!empty($jenisPembayaranTipe))
        <div class="customer">
            <table>
                <tr>
                    <th>Jenis Pembayaran</th>
                    <td></td>
                    <td>{{ $data->first()->jenis_pembayaran->nama_pembayaran }}</td>
                </tr>
                <tr>
                    <th>Tahun Pelajaran</th>
                    <td></td>
                    <td>{{ $data->first()->jenis_pembayaran->tahunajaran->tahun_ajaran }}</td>
                </tr>
                <tr>
                    <th>Nominal</th>
                    <td></td>
                    <td>Rp. {{ number_format($data->first()->jenis_pembayaran->harga) }}</td>
                </tr>
                <tr>
                    <th>Tipe</th>
                    <td></td>
                    <td>{{ $jenisPembayaranTipe === 'bulanan' ? 'Bulanan' : 'Angsuran/Bebas' }}</td>
                </tr>
                <tr>
                    <th>Kelas</th>
                    <td></td>
                    <td>{{ $namaKelas == '' ? 'Semua Kelas' : $namaKelas }}</td>
                </tr>
            </table>
        </div>
        {{-- <p></p> --}}
    @else
        <p>Dicetak Tanggal : {{ date('d-m-Y') }}</p>
        <p></p>
        <p></p>
    @endif
    <div class="page">
        <table>
            <thead>
                <tr>
                    <th style="text-align:center; border: 1px solid black">#</th>
                    <th style="text-align:center; border: 1px solid black">Nis</th>
                    <th style="text-align:center; border: 1px solid black">Nama</th>
                    <th style="text-align:center; border: 1px solid black">Kelas</th>

                    @if ($jenisPembayaranTipe === 'bulanan')
                        @foreach ($bulan as $item)
                            <th style="text-align:center; border: 1px solid black">{{ $item }}</th>
                        @endforeach
                        <th style="text-align:center; border: 1px solid black">Total</th>
                        <th style="text-align:center; border: 1px solid black">Sisa</th>
                    @else
                        <th style="text-align:center; border: 1px solid black">
                            Status
                        </th>
                        <th style="text-align:center; border: 1px solid black">Total Bayar</th>
                        <th style="text-align:center; border: 1px solid black">Sisa</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotal = 0;
                    $totalBayar = 0;
                    $totalSisa = 0;
                    $sisa = 0;
                @endphp
                @forelse ($data as $row)
                    <tr>
                        <td style="text-align:center; border: 1px solid black">{{ $loop->iteration }}</td>

                        <td style="text-align:center; border: 1px solid black">{{ $row->siswa->nis }}</td>

                        <td style="text-align:left; border: 1px solid black">{{ $row->siswa->nama_lengkap }}</td>

                        <td style="text-align:left; border: 1px solid black">
                            {{ $row->siswa->kelas->nama_kelas }}
                        </td>

                        @php
                            $total = 0;
                        @endphp

                        @foreach ($row->tagihan_detail as $item)
                            <td style="text-align:center; border: 1px solid black;">
                                @if ($item->status === 'Lunas')
                                    Lunas
                                    @php
                                        $total = $total + $data->first()->jenis_pembayaran->harga;
                                    @endphp
                                @endif

                                @if ($item->status === 'Belum Lunas')
                                    -
                                    <br>
                                    <small style="margin-bottom: 0">Dibayar: Rp.
                                        Rp. {{ number_format($item->total_bayar) }}</small>
                                    <br>
                                    <small style="margin-top: 0">Sisa: Rp.
                                        Rp. {{ number_format($item->sisa) }}</small>
                                    @php
                                        $sisa += $item->sisa;
                                        $total += $item->total_bayar;
                                    @endphp
                                @endif

                            </td>
                        @endforeach
                        @if ($jenisPembayaranTipe !== 'bulanan')
                            <td style="text-align:right; border: 1px solid black">
                                Rp. {{ number_format($row->tagihan_detail[0]->total_bayar) }}

                                @if ($row->tagihan_detail[0]->total_bayar != 0)
                                    @php
                                        $totalBayar = $totalBayar + $row->tagihan_detail[0]->total_bayar;
                                    @endphp
                                @endif
                            </td>
                            <td style="text-align:right; border: 1px solid black">
                                Rp. {{ number_format($row->tagihan_detail[0]->sisa) }}
                            </td>
                        @endif


                        @php
                            $grandTotal += $total;
                            $totalSisa += $sisa;
                        @endphp

                        @if ($jenisPembayaranTipe === 'bulanan')
                            <td style="text-align:right; border: 1px solid black;">Rp. {{ number_format($total) }}
                            </td>

                            <td style="text-align:right; border: 1px solid black;">Rp. {{ number_format($sisa) }}
                            </td>
                            @php
                                // set sisa ke 0 lagi
                                $sisa = 0;
                            @endphp
                        @endif
                        {{-- $jenisPembayaranTipe === 'bulanan' --}}
                    </tr>


                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse

                @if ($jenisPembayaranTipe === 'bulanan')
                    <tr>
                        <td colspan="9" style="text-align:right; border: 1px solid black;"></td>

                        <td colspan="1" style="text-align:right; border: 1px solid black;">Grand Total </td>

                        <td style="text-align:right; border: 1px solid black;">Rp. {{ number_format($grandTotal) }}
                        </td>

                        <td style="text-align:right; border: 1px solid black;">
                            Rp. {{ number_format($totalSisa) }}
                        </td>
                    </tr>
                @else
                    <tr>
                        {{-- <td colspan="14"style="text-align:right; border: 1px solid black;"></td> --}}
                        <td colspan="5" style="text-align:right; border: 1px solid black;">Grand Total </td>

                        <td style="text-align:right; border: 1px solid black;">Rp. {{ number_format($totalBayar) }}
                        </td>

                        <td style="text-align:right; border: 1px solid black;">
                            Rp. {{ number_format($totalSisa) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>

</html>
