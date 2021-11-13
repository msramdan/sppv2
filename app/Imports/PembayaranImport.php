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
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class PembayaranImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */

    public function __construct($jenis)
    {
        $this->jenis = $jenis;
    }

    public function collection(Collection $rows)
    {

        $jenis =$this->jenis;
        $position = 1;
        $gagal = [];
        $tagihan_kosong = [];
        foreach ($rows as $row) {
            $position++;
            $siswa = Siswa::where('nama_lengkap', $row['nama_siswa'])->first();
            if ($siswa==null || $siswa=='') {
                array_push($gagal,$row['nama_siswa'].' Baris No. '.$position);
            } else {
            //input ke table transaksi pembayaran


            if ($jenis=='bulanan') {
                // 1.Lopping tagihan details join dengan tipe pembayaran, tahun ajaran, semester
                $semester = $row['semester'];
                $tahun_ajaran = $row['tahun_ajaran'];
                $data_detail =collect(\DB::select("select tagihan_details.*,tagihan.siswa_id, jenis_pembayaran.nama_pembayaran,jenis_pembayaran.harga,jenis_pembayaran.tahunajaran_id,jenis_pembayaran.semester,jenis_pembayaran.tipe,tahunajaran.tahun_ajaran FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id join tahunajaran on tahunajaran.id = jenis_pembayaran.tahunajaran_id where semester='$semester' and tahun_ajaran='$tahun_ajaran' and tipe='bulanan' and siswa_id='$siswa->id'"))->first();
                if ($data_detail ==null) {
                    array_push($tagihan_kosong,$row['nama_siswa'].' Baris No. '.$position );
                }else{
                    $pembayaran = TransaksiPembayaran::create([
                    'kode_pembayaran' => 'LKT-' . rand(),
                    'siswa_id' => $siswa->id,
                    'metode_pembayaran' => 'loket',
                    'status' => 'settlement',
                    'total' => $row['dibayar'],
                    'users_id' => auth()->id(),
                    'token' => $row['no_va_pmb'],
                    'tanggal_bayar' =>  Date::excelToDateTimeObject($row['tanggal_bayar'])->format('Y-m-d H:i:s'), // convert excel timestamp to datetime
                ]);
                // 2. ambil data yg belum lunas
                $row_detail =DB::select("select tagihan_details.*,tagihan.siswa_id, jenis_pembayaran.nama_pembayaran,jenis_pembayaran.harga,jenis_pembayaran.tahunajaran_id,jenis_pembayaran.semester,jenis_pembayaran.tipe,tahunajaran.tahun_ajaran FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id join tahunajaran on tahunajaran.id = jenis_pembayaran.tahunajaran_id where semester='$semester' and tahun_ajaran='$tahun_ajaran' and status='Belum Lunas' and tipe='bulanan' and siswa_id='$siswa->id' ");

                // 3. ambil data yg seharusnya di bayar per bulan
                $harga = $data_detail->harga;

                // 4. yang di bayar dari excel
                $bayar = $row['dibayar'];

                foreach ($row_detail as $value) {
                    $data_looping = DB::select("select * from tagihan_details where id='$value->id'");
                    $sisa = $data_looping[0]->sisa;
                    $kurang = $harga -  $sisa;
                    if ($kurang ==0){
                        $fix_bayar = $harga;
                    }else{
                        $fix_bayar = $sisa;
                    }
                    if($bayar > 0 && $bayar >= $fix_bayar){
                        DB::update("UPDATE tagihan_details SET status ='Lunas',total_bayar = total_bayar + $fix_bayar, sisa = sisa - $fix_bayar  where id='$value->id'");
                    }elseif($bayar > 0 && $bayar < $fix_bayar){
                        DB::update("UPDATE tagihan_details SET total_bayar = total_bayar + $bayar, sisa = sisa - $bayar where id='$value->id'");
                    }
                    $bayar = $bayar - $fix_bayar;
                }

                }

                //kondisi jika sosong

            }elseif($jenis=='bebas') {
                //ambil harga yg harus di bayar
                 $data_detail =collect(\DB::select("select tagihan_details.*,tagihan.siswa_id, jenis_pembayaran.nama_pembayaran,jenis_pembayaran.harga,jenis_pembayaran.tahunajaran_id,jenis_pembayaran.semester,jenis_pembayaran.tipe,tahunajaran.tahun_ajaran FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id join tahunajaran on tahunajaran.id = jenis_pembayaran.tahunajaran_id where tipe='bebas' and status='Belum Lunas' and siswa_id='$siswa->id' "))->first();
                 // dd($data_detail);

                if ($data_detail ==null) {
                    array_push($tagihan_kosong,$row['nama_siswa'].' Baris No. '.$position);
                } else {
                    $pembayaran = TransaksiPembayaran::create([
                        'kode_pembayaran' => 'LKT-' . rand(),
                        'siswa_id' => $siswa->id,
                        'metode_pembayaran' => 'loket',
                        'status' => 'settlement',
                        'total' => $row['dibayar'],
                        'users_id' => auth()->id(),
                        'token' => $row['no_va_pmb'],
                        'tanggal_bayar' =>  Date::excelToDateTimeObject($row['tanggal_bayar'])->format('Y-m-d H:i:s'),
                    ]);

                    $harga = $data_detail->harga;
                    $id_tagihan = $data_detail->id;
                    $totalBayar = $data_detail->total_bayar;
                    $bayar = $row['dibayar'];
                        if ($totalBayar + $bayar >= $harga ) {
                            $status ="Lunas";
                            DB::update("UPDATE tagihan_details SET status ='$status',total_bayar = '$harga', sisa ='0'  where id='$id_tagihan'");
                        } elseif($totalBayar + $bayar < $harga ) {
                            $status ="Belum Lunas";
                            DB::update("UPDATE tagihan_details SET status ='$status',total_bayar = total_bayar + $bayar, sisa = sisa - $bayar  where id='$id_tagihan'");
                        }
                    }

                    //kondisi jika kososng
                }
            }

            // input detail pembayaran
            // $pembayaran->detail_pembayaran()->create([
            //     'nama_pembayaran' => $row['nama_pembayaran'],
            //     'keterangan' => $row['keterangan'],
            //     'harga' => $row['dibayar'],
            //     'tagihan_details_id' => $detail_tagihan->id,
            //     'total_bayar' => $total_bayar_detail_pembayaran,
            //     'sisa' => $sisaBayar
            // ]);

            // return $pembayaran;
        }
        $hitung = count($gagal);
        $no_tagihan = count($tagihan_kosong);
        if ($hitung <= 0 && $no_tagihan <= 0){
            alert()->success('Success','Import Pembayaran Berhasil');
        }else{
            $siswa_not_found ="<ul style='list-style-type: none;margin: 0;padding: 0;' ><li>" . implode("</li><li>", $gagal) . "</li></ul>";
            $tidak_ada_tagihan ="<ul style='list-style-type: none;margin: 0;padding: 0;' ><li>" . implode("</li><li>", $tagihan_kosong) . "</li></ul>";
            Alert::html('', '<b>'.'Tidak ada tagihan'.'</b><font style="font-size:12px">'.$tidak_ada_tagihan. '</font><br><b>'. 'Siswa tidak ditemukan'.'</b><font style="font-size:12px">' .$siswa_not_found.'</font>' ,'info')->persistent('Dismiss');
        }
    }
}
