<?php

namespace App\Http\Controllers\Admin;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\JenisPembayaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use App\Models\PengaturanSekolah;
use App\Models\TransaksiPembayaran;
use App\Http\Controllers\Controller;
use App\Imports\PembayaranImport;
use App\Models\TagihanDetail;
use Illuminate\Support\Facades\Auth;

class TransaksiPembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pembayaran = TransaksiPembayaran::with('siswa')->latest();

        if (!empty($request->keyword)) {
            $this->keyword = $request->keyword;

            $pembayaran = $pembayaran->where('kode_pembayaran', 'like', "%" . $this->keyword . "%")
                ->orWhere('metode_pembayaran', 'like', "%" . $this->keyword . "%")
                ->orWhere('status', 'like', "%" . $this->keyword . "%")
                ->orWhere('created_at', 'like', "%" . $this->keyword . "%");

            $pembayaran = $pembayaran->orWhereHas('siswa', function ($query) {
                $query->where('nama_lengkap', 'like', "%" . $this->keyword . "%")
                    ->orWhere('nis', 'like', "%" . $this->keyword . "%");
            });
        }

        return view('admin.pembayaran.index')->with('pembayaran', $pembayaran->where('status', '!=', 'Draft')->paginate(20));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!empty($request->siswa_id)) {
            $bulan = [
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
            ];
            $this->keyword = $request->siswa_id;

            $tagihanSiswa = Siswa::with('tagihan')->findOrFail($this->keyword);

            $siswa = Siswa::all();
            return view('admin.pembayaran.create', compact('siswa', 'tagihanSiswa', 'bulan'));
        }
        $siswa = Siswa::all();
        return view('admin.pembayaran.create', compact('siswa'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $jenisPembayaran = JenisPembayaran::findOrFail($request->jenis_pembayaran_id);

        $dt = Carbon::now();

        $pembayaran = TransaksiPembayaran::create([
            'siswa_id' => $request->id,
            'tanggal_pembayaran' => $dt->toDateString(),
            'bulan' => $request->bulan,
            'metode_pembayaran' => "Loket",
            'total' => $jenisPembayaran->harga,
            'users_id' => Auth::user()->id,
        ]);

        return redirect(route('pembayaran.index'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $detail = TransaksiPembayaran::with('siswa', 'detail_pembayaran', 'user')->findOrFail($id);
        // return $detail;
        return view('admin.pembayaran.detail', compact('detail'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getSiswa(Request $request)
    {
        $siswa = Siswa::with('kelas', 'tagihan')->where('nama_lengkap', 'like', "%" . $request->keyword . "%")
            ->orWhere('nis', 'like', "%$request->keyword%");

        return response()->json($siswa->get(), 200);
    }

    public function getTagihan($id)
    {
        $tagihan = Tagihan::with('siswa', 'tagihan_detail', 'jenis_pembayaran')->where('siswa_id', $id);
        return response()->json($tagihan->get(), 200);
    }

    public function cetak($id)
    {
        $detail = TransaksiPembayaran::with('siswa', 'detail_pembayaran')->findOrFail($id);
        $sekolahInfo = PengaturanSekolah::all()->first();

        return view('admin.pembayaran.cetak', compact('detail', 'sekolahInfo'));
    }

    // public function viewImport()
    // {
    //     $siswa = Siswa::where('nama_lengkap', 'Nova Hassanah')->first();

    //     $jenis_pembayaran = JenisPembayaran::where('nama_pembayaran', 'per siswa nova hassanah')->first();

    //     $tagihan = Tagihan::where('jenis_pembayaran_id', $jenis_pembayaran->id)->where('siswa_id', $siswa->id)->first();

    //     $detail_tagihan = TagihanDetail::where('tagihan_id', $tagihan->id)->where('keterangan', 'juli')->first();

    //     echo json_encode($detail_tagihan);
    //     die;
    // }

    public function importExcel(Request $request)
    {
        $this->validate($request, [
            'import_pembayaran' => 'required|mimes:xls,xlsx'
        ]);

        try {
            Excel::import(new PembayaranImport, $request->file('import_pembayaran'));
            return redirect(route('pembayaran.index'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            session()->flash('error', "Format excel tidak sesuai");
            return redirect(route('pembayaran.index'));
        }
    }
}
