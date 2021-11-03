<?php

namespace App\Http\Controllers;

// use App\Tahunajaran;
use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\JenisPembayaran;
use App\Helper\BulanHelper;
use App\Models\Tahunajaran;
use Illuminate\Http\Request;
use App\Models\TagihanDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\JenisPembayaran\StoreJenisPembayaranRequest;
use App\Models\Detail_pembayaran;
use App\Models\TransaksiPembayaran;

// use App\Http\Requests\JenisPembayaran\UpdateJenisPembayaranRequest;

class JenisPembayaranController extends Controller
{
    public $tagihan;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $jenis_pembayaran = JenisPembayaran::latest();

        if (!empty($request->keyword)) {
            $jenis_pembayaran = $jenis_pembayaran->where('nama_pembayaran', 'like', "%" . $request->keyword . "%");
        }

        return view('admin.jenis_pembayaran.index')->with('jenis_pembayaran', $jenis_pembayaran->paginate(10));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tahun_ajaran = Tahunajaran::all();
        $kelas = Kelas::with('siswa')->get();

        return view('admin.jenis_pembayaran.create')
            ->with('tahun_ajaran', $tahun_ajaran)
            ->with('kelas', $kelas);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreJenisPembayaranRequest $request)
    {
        if (!$request->has('semua_kelas') && !$request->has('semua_siswa_kelas') && !$request->has('per_siswa')) {
            return redirect()->back()->with('error', '"Pembayaran untuk" tidak boleh kosong!, harap pilih salah satu.');
        }

        // string
        if ($request->has('semua_kelas')) {
            $semua_kelas = Kelas::pluck('id')->all();

            $siswa = Siswa::whereIn('kelas_id', $semua_kelas)->where('status', 'Aktif')->get()->pluck('id');

            $this->createOrUpdateBatchTagihan($siswa, $request, 'new', ['semua kelas']);

            session()->flash('success', 'Data Berhasil Disimpan');

            return redirect(route('jenispembayaran.index'));
        }

        // array
        if ($request->has('semua_siswa_kelas')) {
            $kelas = Kelas::whereIn('id', $request->semua_siswa_kelas)->pluck('id')->all();

            $siswa = Siswa::whereIn('kelas_id', $kelas)->where('status', 'Aktif')->get()->pluck('id');

            $this->createOrUpdateBatchTagihan($siswa, $request, 'new', ['semua siswa kelas', $request->semua_siswa_kelas]);

            session()->flash('success', 'Data Berhasil Disimpan');

            return redirect(route('jenispembayaran.index'));
        }

        // array
        if ($request->has('per_siswa')) {

            $siswa = Siswa::whereIn('id', $request->per_siswa)->where('status', 'Aktif')->get()->pluck('id');

            $this->createOrUpdateBatchTagihan($siswa, $request, 'new', ['per siswa', $request->per_siswa]);

            session()->flash('success', 'Data Berhasil Disimpan');

            return redirect(route('jenispembayaran.index'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $jenis_pembayaran = JenisPembayaran::with('tagihan')->findOrFail($id);
        // string to array
        $pembayaran_untuk = json_decode($jenis_pembayaran->pembayaran_untuk);

        $tahun_ajaran = Tahunajaran::all();

        $kelas = Kelas::with('siswa')->get();

        return view('admin.jenis_pembayaran.edit', compact('jenis_pembayaran', 'tahun_ajaran', 'kelas', 'pembayaran_untuk'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreJenisPembayaranRequest $request, $id)
    {
        $update  = $request->validated();
        $jenis_pembayaran = JenisPembayaran::with('tagihan')->findOrFail($id);

        $this->deleteTagihanAndDetailTagihan($id);

        if ($request->has('semua_kelas')) {
            // update jenis pembayaran
            // $update['tipe'] = 'semua kelas';
            // $jenis_pembayaran->update($update);

            $semua_kelas = Kelas::pluck('id')->all();

            $siswa = Siswa::whereIn('kelas_id', $semua_kelas)->where('status', 'Aktif')->get()->pluck('id');

            $this->createOrUpdateBatchTagihan($siswa, $request, $jenis_pembayaran, ['semua kelas']);

            $jenis_pembayaran->update($update);

            session()->flash('success', 'Data Berhasil Diupdate');

            return redirect(route('jenispembayaran.index'));
        }

        if ($request->has('semua_siswa_kelas')) {
            $kelas = Kelas::whereIn('id', $request->semua_siswa_kelas)->pluck('id')->all();

            $siswa = Siswa::whereIn('kelas_id', $kelas)->where('status', 'Aktif')->get()->pluck('id');

            $this->createOrUpdateBatchTagihan($siswa, $request, $jenis_pembayaran, ['semua siswa kelas', $request->semua_siswa_kelas]);

            $jenis_pembayaran->update($update);

            session()->flash('success', 'Data Berhasil Diupdate');

            return redirect(route('jenispembayaran.index'));
        }

        if ($request->has('per_siswa')) {
            $siswa = Siswa::whereIn('id', $request->per_siswa)->where('status', 'Aktif')->get()->pluck('id');

            $this->createOrUpdateBatchTagihan($siswa, $request, $jenis_pembayaran, ['per siswa', $request->per_siswa]);

            $jenis_pembayaran->update($update);

            session()->flash('success', 'Data Berhasil Diupdate');

            return redirect(route('jenispembayaran.index'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $jenisPembayaran = JenisPembayaran::findOrFail($id);

        // return $jenisPembayaran->total_byr;
        if ($jenisPembayaran->lunas > 0 || $jenisPembayaran->total_byr > 0) {
            session()->flash('error', "Gagal menghapus $jenisPembayaran->nama_pembayaran !!");
        } else {
            $this->deleteTagihanAndDetailTagihan($id);
            JenisPembayaran::findOrFail($id)->delete();

            session()->flash('success', "Data Berhasil Dihapus!");
        }

        //redirect user
        return redirect(route('jenispembayaran.index'));
    }

    public function createOrUpdateBatchTagihan($siswa, $request, $jenis_pembayaran, $pembayaran_untuk)
    {
        if ($request->semester ==1) {
            $bulan = BulanHelper::getBulan1();
        } else {
            $bulan = BulanHelper::getBulan2();
        }

        if ($jenis_pembayaran == 'new') {
            $jenis_pembayaran = JenisPembayaran::create([
                'nama_pembayaran' => $request->nama_pembayaran,
                'tahunajaran_id' => $request->tahunajaran_id,
                'tipe' => $request->tipe,
                'harga' => $request->harga,
                'semester' => $request->semester,
                // array to string
                'pembayaran_untuk' => json_encode($pembayaran_untuk),
            ]);
        } else {
            $jenis_pembayaran->update(['pembayaran_untuk' => json_encode($pembayaran_untuk)]);
        }

        /**
         * cek sudah ada tagihan atau belum
         * jika belum maka set $latest_tagihan_id ke 1
         */

        $latest_tagihan_id = Tagihan::orderByDesc('id')->pluck('id')->first();
        $latest_tagihan_id ? $latest_tagihan_id : $latest_tagihan_id = 0;

        $array_tagihan = [];
        $array_tagihan_detail = [];

        foreach ($siswa as $key => $value) {
            // push array
            $array_tagihan[] = [
                'id' => $latest_tagihan_id + $key + 1,
                'siswa_id' => $value,
                'jenis_pembayaran_id' => $jenis_pembayaran->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // insert batch
        Tagihan::insert($array_tagihan);

        foreach ($array_tagihan as $at) {
            if ($request->tipe === "bulanan") {
                foreach ($bulan as $b) {
                    $array_tagihan_detail[] = [
                        'tagihan_id' => $at['id'],
                        'status' => 'Belum Lunas',
                        'keterangan' => $b,
                        'total_bayar' => 0,
                        'sisa' => $jenis_pembayaran->harga,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            } else {
                $array_tagihan_detail[] = [
                    'tagihan_id' => $at['id'],
                    'status' => 'Belum Lunas',
                    'keterangan' => 'Bebas',
                    'total_bayar' => 0,
                    'sisa' => $jenis_pembayaran->harga,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // insert batch
        TagihanDetail::insert($array_tagihan_detail);
    }

    public function deleteTagihanAndDetailTagihan($id)
    {
        $tagihan_lama = Tagihan::where('jenis_pembayaran_id', $id)->get()->pluck('id');

        $tagihan_lama_detail = TagihanDetail::whereIn('tagihan_id', $tagihan_lama)->where('status', 'Lunas')->get()->pluck('id');

        // $detail_pembayaran = Detail_pembayaran::with('transaksi_pembayaran')->whereIn('tagihan_details_id', $tagihan_lama_detail)->get()->pluck('transaksi_pembayaran_id');

        Detail_pembayaran::whereIn('tagihan_details_id', $tagihan_lama_detail)->delete();
        // TransaksiPembayaran::whereIn('id', $detail_pembayaran)->delete();

        Tagihan::where('jenis_pembayaran_id', $id)->delete();
    }
}
