<div>
    <div class="col-md-9 mx-auto">
        <div class="form-group">
            <input type="text" class="form-control form-control-lg" placeholder="Masukkan NIS/Nama Siswa"
                wire:model="search" autofocus>

            @if ($siswa && $search)
                <div class="p-1 rounded mb-0"
                    style="height: auto; width: 100%; color:#555; background-color:#fff;border-color:#61c6ed;outline:0; box-shadow:0 0 0 .2rem rgba(21,140,186,.25); overflow-y: scroll; overflow-y: auto;"
                    wire:loading.attr="disabled" wire:target="siswa">

                    <div wire:loading wire:target="siswa" class="ml-2">Loading...
                    </div>
                    <table class="table table-hover table-striped mb-0">
                        @forelse ($siswa as $sws)
                            <tr>
                                <td style="cursor: pointer" wire:click="selectSiswa({{ $sws->id }})">
                                    {{ '[' . $sws->nis . '] ' . $sws->nama_lengkap . ' - ' . $sws->kelas->nama_kelas }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    <p class="text-center text-danger">Siswa tidak ditemukan!</p>
                                </td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            @endif
        </div>
    </div>
    @dump(\Cart::getContent())

    {{-- info siswa --}}
    @if ($selected_siswa)
        <div class="row">

            <div class="col-md-2">
                <div class="user-item">
                    <img alt="image" height="128px" width="128px" src="../img/siswa/{{ $selected_siswa->foto }}"
                        class="img-fluid">
                    <div class="user-details">
                        <div class="user-name">{{ $selected_siswa->nama_lengkap }}</div>
                        <div class="text-job text-muted">{{ $selected_siswa->nis }}</div>
                        <div class="user-cta">
                            <button class="btn btn-primary follow-btn">{{ $selected_siswa->status }}</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div id="accordion">
                    <label class="text-dark">Per siswa</label>
                    @foreach ($tagihan as $tgh)
                        <div class="accordion">
                            <div class="accordion-header mb-2" role="button" data-toggle="collapse"
                                data-target="#panel-body-{{ $tgh->id }}">
                                <h4>{{ $tgh->jenis_pembayaran->nama_pembayaran . ' - ' . $tgh->jenis_pembayaran->tipe }}
                                </h4>
                            </div>

                            <div class="accordion-body collapse" id="panel-body-{{ $tgh->id }}"
                                data-parent="#accordion">
                                <div class="row">
                                    @foreach ($tgh->tagihan_detail as $td)
                                        @if ($tgh->jenis_pembayaran->tipe === 'bulanan')
                                            <div class="col-md-4">
                                                <div class="card shadow-sm">

                                                    <div class="card-body">
                                                        <div class="small">
                                                            <span>Nominal </span>
                                                            <strong>Rp.{{ number_format($tgh->jenis_pembayaran->harga) }}</strong>
                                                            <br>

                                                            <span>Total Bayar </span>
                                                            <strong
                                                                class="text-success">Rp.{{ number_format($td->total_bayar) }}</strong>
                                                            <br>

                                                            <span>Sisa </span>
                                                            <strong
                                                                class="text-warning">Rp.{{ number_format($td->sisa) }}</strong>

                                                            <hr class="m-0 p-0">
                                                            {{ $td->keterangan }}
                                                        </div>
                                                    </div>

                                                    @if ($td->status == 'Lunas')
                                                        <button class="btn btn-success btn-sm btn-block">
                                                            <i class="fas fa-thumbs-up"></i>
                                                            Lunas
                                                        </button>
                                                    @else
                                                        <button class="btn btn-info btn-sm btn-block" @forelse (\Cart::getContent() as $cart)  @if ($cart->id
                                                            !=$td->id)
                                                            wire:click="addToCard({{ $td->id }})"
                                                        @else
                                                            disabled @endif
                                                            @empty
                                                                wire:click="addToCard({{ $td->id }})"
                                                        @endforelse>
                                                        <i class="fas fa-money-bill-wave"></i>
                                                        Bayar
                                                        </button>
                                            @endif
                                            {{-- $td->status == 'Lunas' --}}

                                    </div>
                                </div>
                            @else
                                {{-- jika tipe jenis pembayaran tipe bebas --}}
                                <div class="col-md-12">
                                    <span>Nominal </span>
                                    <strong>Rp.{{ number_format($tgh->jenis_pembayaran->harga) }}</strong>
                                    <br>

                                    <span>Total Bayar </span>
                                    <strong class="text-success">Rp.{{ number_format($td->total_bayar) }}</strong>
                                    <br>

                                    <span>Sisa </span>
                                    <strong class="text-warning">Rp.{{ number_format($td->sisa) }}</strong>
                                    <br>

                                    <div class="mt-2">
                                        @if ($td->status == 'Lunas')
                                            <button class="btn btn-success btn-sm btn-block">
                                                <i class="fas fa-thumbs-up"></i>
                                                Lunas
                                            </button>
                                        @else
                                            <button class="btn btn-info btn-sm btn-block" @forelse (\Cart::getContent() as $cart)
                                                @if ($cart->id !=$td->id)
                                                wire:click="addToCard({{ $td->id }})"
                                            @else
                                                disabled @endif
                                            @empty
                                                wire:click="addToCard({{ $td->id }})"
                                        @endforelse>
                                        <i class="fas fa-money-bill-wave"></i>
                                        Bayar
                                        </button>
                        @endif
                        {{-- $td->status == 'Lunas' --}}
                    </div>
                </div>
        @endif
        {{-- $tgh->jenis_pembayaran->tipe === 'bulanan' --}}
        @endforeach
        {{-- $tgh->tagihan_detail as $td --}}
    </div>
    </div>
    </div>
    @endforeach
    {{-- $tagihan as $tgh --}}
    </div>
    </div>


    <div class="col-md-4">
        {{-- @{{tagihan_id}} --}}
        <div class="card card-info pembayaranDetail">

            <div class="card-body">

                <h6 class="card-title">Pembayaran Detail</h6>
                {{-- <div v-if="submitCart" class="spinner-border text-danger spinner-border-sm" role="status">
                                <span class="sr-only">Loading...</span>
                            </div> --}}

                @forelse (\Cart::getContent() as $cart)
                    <div class="d-flex justify-content-between border-bottom">
                        <div>
                            <p class="mb-0">{{ $cart->name }}</p>
                            <p>{{ $cart->attributes->keterangan }}</p>
                        </div>
                        <div>
                            Rp. {{ number_format($cart->price) }}
                            <button class="btn text-danger" wire:click="removeCart({{ $cart->id }})"><i
                                    class="fas fa-times-circle"></i></button>
                        </div>
                    </div>
                @empty
                    <div class="d-flex justify-content-center">
                        <img alt="image" style="opacity: 0.3" height="90px" width="90px"
                            src="../img/undraw_empty_cart_co35.png">
                    </div>
                @endforelse
                {{-- $cart as $c --}}

                <div class="py-4 ">
                    <h6>Total Bayar</h6>
                    <h4>Rp. {{ number_format(\Cart::getTotal()) }}</h4>
                </div>

                <button class="btn btn- {{ \Cart::getTotal() == 0 ? 'btn-dark' : 'btn-primary' }} btn-block"
                    {{ \Cart::getTotal() == 0 ? 'disabled' : '' }}>Lanjutkan
                    Pembayaran</button>

            </div>
        </div>
    </div>
    <!-- /.col-md -->

    </div>
    @endif
    {{-- ($selected_siswa --}}
    </div>
