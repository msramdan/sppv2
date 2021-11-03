<?php

namespace App\Helper;

use App\Setting;

class BulanHelper
{

    public static function getBulan1()
    {
        $bulan = [
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        return $bulan;
    }

    public static function getBulan2()
    {
        $bulan = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
        ];
        return $bulan;
    }

    public static function getBulanSingkat1()
    {
        $bulan = [
            'Jul',
            'Agus',
            'Sept',
            'Okt',
            'Nov',
            'Des',
        ];
        return $bulan;
    }


    public static function getBulanSingkat2()
    {
        $bulan = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
        ];
        return $bulan;
    }

}
