<?php

namespace App\Http\Livewire;

use App\Models\Siswa;
use App\Models\Tagihan;
use Livewire\Component;
use App\JenisPembayaran;
use App\Models\TagihanDetail;
use App\Models\Tahunajaran;
use Illuminate\Support\Facades\DB;
use App\Models\TransaksiPembayaran;
use Gloudemans\Shoppingcart\Facades\Cart;


class TransaksiPembayaranLivewire extends Component
{
    public $tagihan_id, $siswa, $selected_siswa, $tagihan, $dibayar, $sisa, $nominal, $nominal_string, $keterangan, $nama_pembayaran, $tahun_ajaran, $selected_tahun, $nama_tahun_ajaran, $tanggal_bayar, $flash_message, $dibayar_error, $tanggal_bayar_error, $modal = false, $cart = [];

    public $search = '';

    public function mount()
    {
        $this->reset();
    }

    public function reset(...$properties)
    {
        $this->search = '';
        $this->siswa = '';
        $this->flash_message = '';
        $this->dibayar = '';
        $this->tanggal_bayar_error = '';
        $this->dibayar_error = '';
        $this->tanggal_bayar = '';
    }

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function render()
    {
        return view('livewire.transaksi-pembayaran-livewire');
    }

    public function updatedSearch()
    {
        // if ($this->search != '') {
        $this->siswa = Siswa::with('kelas')
            ->where('nama_lengkap', 'like', '%' . $this->search . '%')
            ->orWhere('nis', 'like', '%' . $this->search . '%')->limit(5)->get();
        // }
    }

    public function selectSiswa($id)
    {
        $this->selected_siswa = Siswa::with('kelas')->findOrFail($id);

        $this->tagihan = Tagihan::with('tagihan_detail', 'jenis_pembayaran')->where('siswa_id', $id)->get();

        $this->selected_tahun = 'semua';
        $this->tahun_ajaran = Tahunajaran::get();
        // dd($this->tagihan);

        $this->search = '';
        $this->siswa = '';
        $this->flash_message = '';
        $this->tanggal_bayar_error = '';
        $this->dibayar_error = '';
        $this->tanggal_bayar = '';

        Cart::session(auth()->id())->clear();
    }

    public function updatedSelectedTahun()
    {
        $tahun = $this->selected_tahun;

        if ($tahun == 'semua') {
            $this->tagihan = Tagihan::with('siswa', 'tagihan_detail', 'jenis_pembayaran')->where('siswa_id', $this->selected_siswa->id)->get();
        } else {
            $this->tagihan = Tagihan::with('siswa', 'tagihan_detail', 'jenis_pembayaran')->whereHas('jenis_pembayaran', function ($q) use ($tahun) {
                $q->where('tahunajaran_id', $tahun);
            })->where('siswa_id', $this->selected_siswa->id)->get();

            $this->nama_tahun_ajaran = Tahunajaran::findOrFail($tahun);
        }
    }

    public function addToCart()
    {
        if ($this->dibayar == '') {
            $this->dibayar_error = 'Dibayar tidak boleh kosong!';
        } else {
            $sisa = $this->sisa - $this->dibayar;
            if ($sisa <= 0) {
                $sisa = 0;
                $this->dibayar = $this->nominal;
            }

            Cart::session(auth()->id())->add([
                'id' => $this->tagihan_id,
                'name' => $this->nama_pembayaran,
                'price' => $this->nominal,
                'quantity' => 1,
                'attributes' => [
                    'keterangan' => $this->keterangan,
                    'dibayar' => $this->dibayar,
                    'sisa' => $this->sisa - $this->dibayar,
                ],
            ]);

            $this->closeModal();
        }
    }

    public function showModal($id)
    {
        $this->dibayar = '';
        $this->tanggal_bayar_error = '';
        $this->dibayar_error = '';
        $this->modal = true;

        $tagihan_detail = TagihanDetail::with('tagihan')->findOrFail($id);

        $jenis_pembayaran = JenisPembayaran::findOrFail($tagihan_detail->tagihan->jenis_pembayaran_id);

        $this->tagihan_id = $id;
        $this->nama_pembayaran = $jenis_pembayaran->nama_pembayaran;
        $this->nominal = $tagihan_detail->sisa;
        $this->nominal_string = 'Rp. ' . number_format($tagihan_detail->sisa);
        $this->keterangan = $tagihan_detail->keterangan;
        $this->sisa = $tagihan_detail->sisa;

        // dd(Cart::getContent());
    }

    public function closeModal()
    {
        $this->modal = false;
        $this->dibayar = '';
        $this->tanggal_bayar_error = '';
        $this->dibayar_error = '';
        // $this->tanggal_bayar = '';
    }

    public function saveTransaksi($cart)
    {
        $grand_total = 0;
        foreach ($cart as $row) {
            $grand_total += $row['attributes']['dibayar'];
        }

        if ($this->tanggal_bayar == '') {
            $this->tanggal_bayar_error = 'Tanggal Bayar tidak boleh kosong!';
        } else {
            DB::beginTransaction();

            try {

                $kode = 'LKT-' . rand();
                //menyimpan data ke table
                $pembayaran = TransaksiPembayaran::create([
                    'siswa_id' => $this->selected_siswa->id,
                    'kode_pembayaran' => $kode,
                    'metode_pembayaran' => 'Loket',
                    'total' => $grand_total,
                    'status' => 'settlement',
                    'users_id' => auth()->id(),
                    'tanggal_bayar' => $this->tanggal_bayar
                ]);

                foreach ($cart as $row) {
                    // dd($row['id']);
                    $tagihanDetail = TagihanDetail::findOrFail($row['id']);

                    $totalBayar = $tagihanDetail->total_bayar + $row['attributes']['dibayar'];
                    $sisaBayar = $tagihanDetail->sisa - $row['attributes']['dibayar'];

                    $tagihanDetail->sisa = $sisaBayar;

                    if ($sisaBayar == 0) {
                        $tagihanDetail->status = "Lunas";
                    }

                    if ($tagihanDetail->total_bayar != 0) {
                        $total_bayar_detail_pembayaran = $row['attributes']['dibayar'];
                    } else {
                        $total_bayar_detail_pembayaran = $totalBayar;
                    }

                    $tagihanDetail->total_bayar = $totalBayar;

                    $tagihanDetail->update();

                    $tes = $pembayaran->detail_pembayaran()->create([
                        'nama_pembayaran' => $row['name'],
                        'keterangan' => $row['attributes']['keterangan'],
                        'harga' => $row['price'],
                        'tagihan_details_id' => $row['id'],
                        'total_bayar' => $total_bayar_detail_pembayaran,
                        'sisa' => $sisaBayar
                    ]);
                }
                //apabila tidak terjadi error, penyimpanan diverifikasi
                DB::commit();

                Cart::session(auth()->id())->clear();
                $this->selected_siswa = '';
                $this->tanggal_bayar = '';
                $this->dibayar_error = '';

                $this->flash_message = "Transaksi Pembayaran Berhasil Disimpan";
            } catch (\Exception $e) {
                //jika ada error, maka akan dirollback sehingga tidak ada data yang tersimpan
                DB::rollback();
                dd($e->getMessage());
            }
        }
    }

    public function removeCart($id)
    {
        Cart::session(auth()->id())->remove($id);
        $this->tanggal_bayar_error = '';
    }
}
