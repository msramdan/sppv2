<?php

namespace App\Http\Livewire;

use App\Models\Siswa;
use App\Models\Tagihan;
use Livewire\Component;
use App\JenisPembayaran;
use Illuminate\Support\Str;
use App\Models\TagihanDetail;
use Gloudemans\Shoppingcart\Facades\Cart;

class TransaksiPembayaranLivewire extends Component
{
    public $siswa, $selected_siswa, $tagihan, $cart = [];

    public $search = '';

    public function mount()
    {
        $this->reset();
    }

    public function reset(...$properties)
    {
        $this->search = '';
        $this->siswa = '';
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
        if ($this->search != '') {
            $this->siswa = Siswa::with('kelas')->where('nama_lengkap', 'like', '%' . $this->search . '%')
                ->orWhere('nis', 'like', '%' . $this->search . '%')->take(5)->get();
        }
    }

    public function selectSiswa($id)
    {
        $this->selected_siswa = Siswa::with('kelas')->findOrFail($id);

        $this->tagihan = Tagihan::with('siswa', 'tagihan_detail', 'jenis_pembayaran')->where('siswa_id', $this->selected_siswa->id)->get();

        $this->search = '';
        $this->siswa = '';

        Cart::session(auth()->id())->clear();
    }

    public function addToCard($id)
    {
        // $tagihan = Tagihan::with('jenis_pembayaran', 'tagihan_detail')->whereHas('tagihan_detail', function ($q) use ($id) {
        //     $q->where('id', $id)->limit(1);
        // })->first();

        $tagihan_detail = TagihanDetail::with('tagihan')->findOrFail($id);

        $jenis_pembayaran = JenisPembayaran::findOrFail($tagihan_detail->tagihan->jenis_pembayaran_id);

        if (count($this->cart) > 0) {
            foreach ($this->cart as $c) {
                if ($c->id != $id) {
                    // add the product to cart
                    Cart::session(auth()->id())->add([
                        'id' => $id,
                        'name' => $jenis_pembayaran->nama_pembayaran,
                        'price' => $tagihan_detail->sisa,
                        'quantity' => 1,
                        'attributes' => ['keterangan' => $tagihan_detail->keterangan],
                        // 'associatedModel' => $tagihan
                    ]);
                }
            }
        } else {
            Cart::session(auth()->id())->add([
                'id' => $id,
                'name' => $jenis_pembayaran->nama_pembayaran,
                'price' => $tagihan_detail->sisa,
                'quantity' => 1,
                'attributes' => ['keterangan' => $tagihan_detail->keterangan]
            ]);
        }


        // if (count($this->cart) > 0) {
        //     foreach ($this->cart as $ct) {
        //         if ($ct['id'] !== $id) {
        //             // foreach ($tagihan as $tg) {
        //             //     $this->cart['tagihan_id'] = $tagihan->id;
        //             //     $this->cart['']
        //             // }
        //             $this->cart['id_cart'] = $id;
        //             $this->cart[] = $tagihan;
        //         } else {
        //             dd('udah ada');
        //         }
        //     }
        //     // dd('ada');
        // } else {
        //     // dd('not isset');
        //     $this->cart[] = $tagihan;
        // }

        // dd($this->cart);

        // dd($this->cart);
        // array_push($this->cart, $tagihan);
    }

    public function removeCart($id)
    {
        Cart::session(auth()->id())->remove($id);
    }
}
