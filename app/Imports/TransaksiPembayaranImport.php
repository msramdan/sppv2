<?php

namespace App\Imports;

use App\Models\TagihanDetail;
use Illuminate\Support\Collection;
use App\Models\TransaksiPembayaran;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TransaksiPembayaranImport implements ToCollection, WithHeadingRow
{

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $pembayaran =  TransaksiPembayaran::create([
                'kode_pembayaran' => 'LKT-' . rand(),
                'siswa_id' => $row['siswa_id'],
                'metode_pembayaran' => $row['metode_pembayaran'],
                'status' => $row['status'],
                'total' => $row['dibayar'],
                'users_id' => auth()->id(),
                'token' => $row['nomor_va_pmb']
            ]);

            $tagihanDetail = TagihanDetail::findOrFail($row['tagihan_details_id']);

            $totalBayar = $tagihanDetail->total_bayar + $row['dibayar'];
            $sisaBayar = $tagihanDetail->sisa - $row['dibayar'];

            $tagihanDetail->sisa = $sisaBayar;

            if ($sisaBayar == 0) {
                $tagihanDetail->status = "Lunas";
            }

            if ($tagihanDetail->total_bayar != 0) {
                $total_bayar_detail_pembayaran = $row['dibayar'];
            } else {
                $total_bayar_detail_pembayaran = $totalBayar;
            }

            $tagihanDetail->total_bayar = $totalBayar;

            $tagihanDetail->update();

            $pembayaran->detail_pembayaran()->create([
                'nama_pembayaran' => $row['nama_pembayaran'],
                'keterangan' => $row['keterangan'],
                'harga' => $row['dibayar'],
                'tagihan_details_id' => $row['tagihan_details_id'],
                'total_bayar' => $total_bayar_detail_pembayaran,
                'sisa' => $sisaBayar
            ]);
        }
    }
}
