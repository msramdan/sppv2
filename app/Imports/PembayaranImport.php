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


    public function collection(Collection $rows)
    {

        $position = 1;
        $gagal = [];
        $tagihan_kosong = [];
        $no_va_notFound = [];
        foreach ($rows as $row) {
            $position++;
            $siswa = Siswa::where('nama_lengkap', $row['nama_siswa'])->first();
            if ($siswa==null || $siswa=='') {
                array_push($gagal,$row['nama_siswa'].' Baris No. '.$position);
            } else {
            // cek no va dari akse sama dengan va spp / va other
            $no_va_excel = $row['no_va'];
            if ($siswa->no_va_spp ==$no_va_excel){
                // 1.Lopping tagihan details join dengan tipe pembayaran
                $data_detail =collect(\DB::select("select tagihan_details.*,tagihan.siswa_id, jenis_pembayaran.nama_pembayaran,jenis_pembayaran.harga,jenis_pembayaran.tipe FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id where tipe='bulanan' and siswa_id='$siswa->id'"))->first();
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
                    'token' => $row['no_va'],
                    'tanggal_bayar' =>  Date::excelToDateTimeObject($row['tanggal_bayar'])->format('Y-m-d H:i:s'), // convert excel timestamp to datetime
                ]);
                // 2. ambil data yg belum lunas
                $row_detail =DB::select("select tagihan_details.*,tagihan.siswa_id, jenis_pembayaran.nama_pembayaran,jenis_pembayaran.harga,jenis_pembayaran.tipe FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id where status='Belum Lunas' and tipe='bulanan' and siswa_id='$siswa->id' ");
                // 3. ambil data yg seharusnya di bayar per bulan
                $harga = $data_detail->harga;
                // 4. yang di bayar dari excel
                $bayar = $row['dibayar'];
                foreach ($row_detail as $value) {
                    if($bayar > 0 ){
                    $data_looping = DB::select("select * from tagihan_details where id='$value->id'");
                    $sisa = $data_looping[0]->sisa;
                    //ambil data detail tagihan
                    $detail_tagihan =DB::select("select tagihan_details.*,jenis_pembayaran.nama_pembayaran FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id where tagihan_details.id='$value->id'");
                    if($bayar >= $sisa){
                        $fix_bayar = $sisa;
                        DB::update("UPDATE tagihan_details SET status ='Lunas',total_bayar = total_bayar + $fix_bayar, sisa = sisa - $fix_bayar  where id='$value->id'");
                        // input detail pembayaran
                                    $pembayaran->detail_pembayaran()->create([
                                        'nama_pembayaran' => $detail_tagihan[0]->nama_pembayaran,
                                        'keterangan' => $detail_tagihan[0]->keterangan,
                                        'harga' => $detail_tagihan[0]->sisa,
                                        'tagihan_details_id' => $value->id, //ok
                                        'total_bayar' => $fix_bayar, //ok
                                        'sisa' => $detail_tagihan[0]->sisa - $fix_bayar
                                    ]);
                    }elseif($bayar < $sisa){
                        $fix_bayar = $bayar;
                        DB::update("UPDATE tagihan_details SET total_bayar = total_bayar + $fix_bayar, sisa = sisa - $fix_bayar where id='$value->id'");
                        // input detail pembayaran
                                    $pembayaran->detail_pembayaran()->create([
                                        'nama_pembayaran' => $detail_tagihan[0]->nama_pembayaran,
                                        'keterangan' => $detail_tagihan[0]->keterangan,
                                        'harga' => $detail_tagihan[0]->sisa,
                                        'tagihan_details_id' => $value->id, //ok
                                        'total_bayar' => $fix_bayar, //ok
                                        'sisa' => $detail_tagihan[0]->sisa - $fix_bayar
                                    ]);
                    }
                    $bayar = $bayar - $fix_bayar;
                }
            }

                }

            }elseif ($siswa->no_va_other ==$no_va_excel){
                //ambil harga yg harus di bayar
                 $data_detail =collect(\DB::select("select tagihan_details.*,tagihan.siswa_id, jenis_pembayaran.nama_pembayaran,jenis_pembayaran.harga,jenis_pembayaran.tipe FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id where tipe='bebas' and status='Belum Lunas' and siswa_id='$siswa->id' "));
                //looping tagihan detail untuk ambil harga
                if ($data_detail ==null|| $data_detail =='') {
                    array_push($tagihan_kosong,$row['nama_siswa'].' Baris No. '.$position);
                } else {
                    $pembayaran = TransaksiPembayaran::create([
                        'kode_pembayaran' => 'LKT-' . rand(),
                        'siswa_id' => $siswa->id,
                        'metode_pembayaran' => 'loket',
                        'status' => 'settlement',
                        'total' => $row['dibayar'],
                        'users_id' => auth()->id(),
                        'token' => $row['no_va'],
                        'tanggal_bayar' =>  Date::excelToDateTimeObject($row['tanggal_bayar'])->format('Y-m-d H:i:s'),
                    ]);
                        $bayar = $row['dibayar'];
                        foreach ($data_detail as $value) {
                            if($bayar > 0 ){
                                $harga = $value->harga; //10.000
                                $id_tagihan = $value->id; //1
                                $sisa = $value->sisa; //10000
                                //ambil data detail tagihan
                                $detail_tagihan =DB::select("select tagihan_details.*,jenis_pembayaran.nama_pembayaran FROM tagihan_details JOIN tagihan ON tagihan_details.tagihan_id = tagihan.id
                                            join jenis_pembayaran on jenis_pembayaran.id = tagihan.jenis_pembayaran_id where tagihan_details.id='$id_tagihan'");
                                if ($bayar >= $sisa){
                                    $fix_bayar = $sisa;
                                    $status ="Lunas";
                                    DB::update("UPDATE tagihan_details SET status ='$status',total_bayar = total_bayar + $fix_bayar, sisa = sisa - $fix_bayar  where id='$id_tagihan'");
                                    // input detail pembayaran
                                    $pembayaran->detail_pembayaran()->create([
                                        'nama_pembayaran' => $detail_tagihan[0]->nama_pembayaran,
                                        'keterangan' => $detail_tagihan[0]->keterangan,
                                        'harga' => $detail_tagihan[0]->sisa,
                                        'tagihan_details_id' => $id_tagihan, //ok
                                        'total_bayar' => $fix_bayar, //ok
                                        'sisa' => $detail_tagihan[0]->sisa - $fix_bayar
                                    ]);
                                }elseif($bayar < $sisa){
                                    $fix_bayar = $bayar;
                                    $status ="Belum Lunas";
                                    DB::update("UPDATE tagihan_details SET status ='$status',total_bayar = total_bayar + $fix_bayar, sisa = sisa - $fix_bayar  where id='$id_tagihan'");
                                    // input detail pembayaran
                                    $pembayaran->detail_pembayaran()->create([
                                        'nama_pembayaran' => $detail_tagihan[0]->nama_pembayaran,
                                        'keterangan' => $detail_tagihan[0]->keterangan,
                                        'harga' => $detail_tagihan[0]->sisa,
                                        'tagihan_details_id' => $id_tagihan, //ok
                                        'total_bayar' => $fix_bayar, //ok
                                        'sisa' => $detail_tagihan[0]->sisa - $fix_bayar
                                    ]);
                                }

                            }
                            $bayar = $bayar - $fix_bayar;
                        }

                    }
                }elseif($siswa->no_va_spp !=$no_va_excel && $siswa->no_va_other !=$no_va_excel){
                    array_push($no_va_notFound,$row['no_va'].' Baris No. '.$position);
                }
            }

        }
        $hitung = count($gagal);
        $no_tagihan = count($tagihan_kosong);
        $hitung_no_va_notFound = count($no_va_notFound);
        if ($hitung <= 0 && $no_tagihan <= 0 && $hitung_no_va_notFound<=0 ){
            alert()->success('Success','Import Pembayaran Berhasil');
        }else{
            $siswa_not_found ="<ul style='list-style-type: none;margin: 0;padding: 0;' ><li>" . implode("</li><li>", $gagal) . "</li></ul>";
            $tidak_ada_tagihan ="<ul style='list-style-type: none;margin: 0;padding: 0;' ><li>" . implode("</li><li>", $tagihan_kosong) . "</li></ul>";
            $hitung_no_va_notFound_data ="<ul style='list-style-type: none;margin: 0;padding: 0;' ><li>" . implode("</li><li>", $no_va_notFound) . "</li></ul>";
            Alert::html('', '<b>'.'Siswa tidak ditemukan'.'</b><font style="font-size:12px">'.$siswa_not_found. '</font><br><b>'.
            'No VA tidak ditemukan'.'</b><font style="font-size:12px">' .$hitung_no_va_notFound_data.'</font><br><b>'.
            'Tidak ada tagihan'.'</b><font style="font-size:12px">' .$tidak_ada_tagihan.'</font>'
            ,'info')->persistent('Dismiss');
        }
    }
}
