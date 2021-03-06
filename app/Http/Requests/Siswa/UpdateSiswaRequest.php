<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request()->input('id');
        return [
            'nis' => 'required|string|max:50|unique:siswa,nis,'.$id,
            'nama_lengkap' => 'required|string|max:100',
            'jenis_kelamin' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'no_telp' => 'required',
            'alamat' => 'required',
            'nama_ibu_kandung' => 'required',
            'nama_ayah_kandung' => 'required',
            'no_telp_orangtua' => 'required',
            'kelas_id' => 'required',
            'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2000',
            'no_va_spp' =>'nullable|unique:siswa,no_va_spp,' .$id,
            'no_va_other'=>'nullable|unique:siswa,no_va_other,' .$id
        ];
    }
}
