<?php

namespace App\Imports;

use App\User;
use App\Models\Kelas;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use RealRashid\SweetAlert\Facades\Alert;
// use Maatwebsite\Excel\Concerns\WithBatchInserts;
// use Maatwebsite\Excel\Concerns\WithLimit;


class SiswaImport implements ToCollection, WithHeadingRow
{

    public function collection(Collection $rows)
    {
        $kangramdan = [];
        $position = 1;
        foreach ($rows as $row) {
            $position++;
            $no_va_spp =$row['no_va_spp'];
            $no_va_other =$row['no_va_other'];
            $data_cek = DB::select("select * from siswa where no_va_spp='$no_va_spp' or no_va_other='$no_va_other'");
            if($data_cek==''|| $data_cek==null || $data_cek==0){
                    $tgl_lahir = date_format(date_create($row['tanggal_lahir']), "Y-m-d");
                    $jk = ($row['jk'] === 'L') ? 'male' : 'female';

                    $kelas = Kelas::where('nama_kelas', '=', $row['kelas'])->get()->first();

                    if (empty($kelas)) {

                        $kelas = Kelas::create([
                            'nama_kelas' => $row['kelas']
                        ]);
                    }

                    $tesid = uniqid();

                    $user = User::create([
                        'name' => $row['nama'],
                        'username' => $row['nis'],
                        'email' => "email-$tesid@example.com",
                        'password'  => bcrypt('123456'),
                        'status'  => true,
                    ]);

                    $user->assignRole('siswa');

                    $siswa = Siswa::create([
                        'nis' => $row['nis'],
                        'nama_lengkap' => $row['nama'],
                        'jenis_kelamin' => $jk,
                        'tempat_lahir' => $row['tempat_lahir'],
                        'tanggal_lahir' => $tgl_lahir,
                        'no_telp' => $row['no_telp'],
                        'alamat' => $row['alamat'],
                        'nama_ibu_kandung' => $row['nama_ibu_kandung'],
                        'nama_ayah_kandung' => $row['nama_ayah_kandung'],
                        'no_telp_orangtua' => $row['no_telp_orang_tua'],
                        'no_va_spp' => $row['no_va_spp'],
                        'no_va_other' => $row['no_va_other'],
                        'status' => 'Aktif',
                        'kelas_id' => $kelas->id,
                        'user_id' => $user->id,
                        'foto' => 'siswa_default.png',
                    ]);
            }else{
                array_push($kangramdan,$row['nama'].' Baris No. '.$position);
            }

        }

        $data_hitung = count($kangramdan);
        // dd($data_hitung);
        if ($data_hitung > 0){
            $gagal_siswa ="<ul style='list-style-type: none;margin: 0;padding: 0;' ><li>" . implode("</li><li>", $kangramdan) . "</li></ul>";
            Alert::html('', '<b>'.'Siswa gagal import : Kroscek VA Spp & VA Other'.'</b><font style="font-size:12px">'.$gagal_siswa. '</font>'
            ,'info')->persistent('Dismiss');
        }elseif($data_hitung =='' || $data_hitung==0){
            session()->flash('success', "Import Berhasil");
        }


    }

}
