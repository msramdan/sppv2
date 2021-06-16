<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\JenisPembayaran;
use App\Models\Tahunajaran;
use App\Models\TagihanDetail;
use Illuminate\Support\Collection;
use App\Models\TransaksiPembayaran;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PembayaranImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $siswa = Siswa::where('nama_lengkap', $row['nama_siswa'])->first();

            $pembayaran = TransaksiPembayaran::create([
                'kode_pembayaran' => 'LKT-' . rand(),
                'siswa_id' => $siswa->id,
                'metode_pembayaran' => $row['metode_pembayaran'],
                'status' => $row['status'],
                'total' => $row['dibayar'],
                'users_id' => auth()->id(),
                'token' => $row['no_va_pmb'],
                // convert excel timestamp to datetime
                'tanggal_bayar' =>  Date::excelToDateTimeObject($row['tanggal_bayar'])->format('Y-m-d H:i:s'),
            ]);

            $tahun_ajaran = Tahunajaran::where('tahun_ajaran', $row['tahun_ajaran'])->first();

            $jenis_pembayaran = JenisPembayaran::where('nama_pembayaran', $row['nama_pembayaran'])->where('tahunajaran_id', $tahun_ajaran->id)->first();

            // dd($jenis_pembayaran);

            $tagihan = Tagihan::where('jenis_pembayaran_id', $jenis_pembayaran->id)->where('siswa_id', $siswa->id)->first();

            $detail_tagihan = TagihanDetail::where('tagihan_id', $tagihan->id)->where('keterangan', $row['keterangan'])->first();


            $totalBayar = $detail_tagihan->total_bayar + $row['dibayar'];
            // $totalBayar <= $detail_tagihan->sisa ? $totalBayar = $totalBayar : $totalBayar = $detail_tagihan->sisa;

            $sisaBayar = $detail_tagihan->sisa - $row['dibayar'];
            $sisaBayar >= 0 ? $sisaBayar = $sisaBayar : $sisaBayar = 0;

            $detail_tagihan->sisa = $sisaBayar;

            if ($sisaBayar == 0) {
                $detail_tagihan->status = "Lunas";
            }

            if ($detail_tagihan->total_bayar != 0) {
                $total_bayar_detail_pembayaran = $row['dibayar'];
            } else {
                $total_bayar_detail_pembayaran = $totalBayar;
            }

            $detail_tagihan->total_bayar = $totalBayar;

            $detail_tagihan->update();

            $pembayaran->detail_pembayaran()->create([
                'nama_pembayaran' => $row['nama_pembayaran'],
                'keterangan' => $row['keterangan'],
                'harga' => $row['dibayar'],
                'tagihan_details_id' => $detail_tagihan->id,
                'total_bayar' => $total_bayar_detail_pembayaran,
                'sisa' => $sisaBayar
            ]);

            // return $pembayaran;
        }
    }
}
