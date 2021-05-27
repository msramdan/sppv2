<?php

namespace App\Http\Controllers;

// use App\Tahunajaran;
use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\JenisPembayaran;
use App\Models\Tahunajaran;
use Illuminate\Http\Request;
use App\Models\TagihanDetail;
use App\Http\Requests\JenisPembayaran\StoreJenisPembayaranRequest;

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

            $this->insertTagihan($siswa, $request);

            session()->flash('success', 'Data Berhasil Disimpan');

            return redirect(route('jenispembayaran.index'));
        }

        // array
        if ($request->has('semua_siswa_kelas')) {
            $kelas = Kelas::whereIn('id', $request->semua_siswa_kelas)->pluck('id')->all();

            $siswa = Siswa::whereIn('kelas_id', $kelas)->where('status', 'Aktif')->get()->pluck('id');

            $this->insertTagihan($siswa, $request);

            session()->flash('success', 'Data Berhasil Disimpan');

            return redirect(route('jenispembayaran.index'));
        }

        // array
        if ($request->has('per_siswa')) {

            $siswa = Siswa::whereIn('id', $request->per_siswa)->where('status', 'Aktif')->get()->pluck('id');

            $this->insertTagihan($siswa, $request);

            session()->flash('success', 'Data Berhasil Disimpan');

            return redirect(route('jenispembayaran.index'));
        }
    }


    public function insertTagihan($siswa, $request)
    {
        $bulan = \BulanHelper::getBulan();

        $jenis_pembayaran = JenisPembayaran::create([
            'nama_pembayaran' => $request->nama_pembayaran,
            'tahunajaran_id' => $request->tahunajaran_id,
            'tipe' => $request->tipe,
            'harga' => $request->harga
        ]);

        /**
         * cek sudah ada tagihan atau belum
         * jika belum maka set $latest_tagihan_id ke 1
         */
        $latest_tagihan_id = Tagihan::orderByDesc('id')->pluck('id')->first();
        $latest_tagihan_id ? $latest_tagihan_id : $latest_tagihan_id = 1;

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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $jenis_pembayaran = JenisPembayaran::with('tagihan')->findOrFail($id);
        $tahun_ajaran = Tahunajaran::all();
        $kelas = Kelas::with('siswa')->get();

        return view('admin.jenis_pembayaran.edit', compact('jenis_pembayaran', 'tahun_ajaran', 'kelas'));
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
        dd($request->all());

        $bulan = \BulanHelper::getBulan();

        $jenisPembayaran = JenisPembayaran::findOrFail($id);

        $jenisPembayaran->update($request->validated());

        $tagihan = Tagihan::with('tagihan_detail')->where('jenis_pembayaran_id', $jenisPembayaran->id)->get();


        if ($request->old_tipe !== $request->tipe) {
            // hapus tagihan detail
            foreach ($tagihan as $item) {
                $tagihanDetail = TagihanDetail::where('tagihan_id', $item->id)->delete();
            }
        } else {
            foreach ($tagihan as $item) {
                $tagihanDetail = TagihanDetail::where('tagihan_id', $item->id);
                $tagihanDetail->update(['sisa' => $request->harga]);
            }
        }

        // if(!empty($request->kelas_id)){
        foreach ($request->kelas_id as $item) {


            if ($item !== 'semua') {
                $siswa = Siswa::where('kelas_id', $item)->where('status', 'Aktif')->get();

                $no = 1;
                foreach ($siswa as $s) {
                    //cek sudah ada tagihan atau belum
                    $cek = Siswa::whereHas('tagihan', function ($query) use ($jenisPembayaran) {
                        $query->where('jenis_pembayaran_id', $jenisPembayaran->id);
                    });
                    $cek = $cek->where('id', $s->id)->get()->first();

                    if (empty($cek)) {
                        $tagihan = Tagihan::create([
                            'siswa_id' => $s->id,
                            'jenis_pembayaran_id' => $jenisPembayaran->id,
                        ]);

                        if ($request->tipe === "bulanan") {
                            foreach ($bulan as $b) {
                                TagihanDetail::create([
                                    'tagihan_id' => $tagihan->id,
                                    'status' => 'Belum Lunas',
                                    'keterangan' => $b,
                                    'total_bayar' => 0,
                                    'sisa' => $jenisPembayaran->harga,
                                ]);
                            }
                        } else {
                            TagihanDetail::create([
                                'tagihan_id' => $tagihan->id,
                                'status' => 'Belum Lunas',
                                'keterangan' => 'Bebas',
                                'total_bayar' => 0,
                                'sisa' => $jenisPembayaran->harga,
                            ]);
                        }
                    } else {
                        if ($request->old_tipe !== $request->tipe) {
                            $tagihanCari = Tagihan::where('siswa_id', $s->id)->where('jenis_pembayaran_id', $jenisPembayaran->id)->get()->first();

                            if ($request->tipe === "bulanan") {
                                foreach ($bulan as $b) {
                                    TagihanDetail::create([
                                        'tagihan_id' => $tagihanCari->id,
                                        'status' => 'Belum Lunas',
                                        'keterangan' => $b,
                                        'total_bayar' => 0,
                                        'sisa' => $jenisPembayaran->harga,
                                    ]);
                                }
                            } else {
                                TagihanDetail::create([
                                    'tagihan_id' => $tagihanCari->id,
                                    'status' => 'Belum Lunas',
                                    'keterangan' => 'Bebas',
                                    'total_bayar' => 0,
                                    'sisa' => $jenisPembayaran->harga,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        foreach ($request->old_kelas_id as $item) {
            if (!in_array($item, $request->kelas_id)) {
                $siswa = Siswa::where('kelas_id', $item)->get();

                foreach ($siswa as $s) {
                    $tagihan = Tagihan::where('jenis_pembayaran_id', $id)->where('siswa_id', $s->id)->each(function ($tagihan) {
                        TagihanDetail::where('tagihan_id', $tagihan->id)->delete();
                        $tagihan->delete();
                    });
                }
            }
        }
        // }

        session()->flash('success', "Data Berhasil Diubah!");

        //redirect user
        return redirect(route('jenispembayaran.index'));
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
            $tagihan = Tagihan::where('jenis_pembayaran_id', $id)->each(function ($tagihan) {
                TagihanDetail::where('tagihan_id', $tagihan->id)->delete();
                $tagihan->delete();
            });

            $jenisPembayaran->delete();

            session()->flash('success', "Data Berhasil Dihapus!");
        }

        //redirect user
        return redirect(route('jenispembayaran.index'));
    }
}
