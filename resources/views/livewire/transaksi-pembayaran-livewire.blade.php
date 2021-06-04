<div>
    <div class="col-md-9 mx-auto">
        <div class="form-group">
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <input type="text" class="form-control" placeholder="Masukkan NIS/Nama Siswa" wire:model="search"
                    autofocus>
            </div>


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
    {{-- @dump(\Cart::session(Auth()->id())->getContent()) --}}

    {{-- @dump($selected_tahun) --}}

    {{-- info siswa --}}
    @if ($selected_siswa)

        <div class="row mt-5">

            <div class="col-md-6">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <img alt="image" width="100%" src="../img/siswa/{{ $selected_siswa->foto }}"
                            class="img-fluid">
                    </div>
                    <div class="col-md-8">
                        <p class="mb-0 mt-0 font-weight-bold">Nama : {{ $selected_siswa->nama_lengkap }}</p>
                        <p class="mb-0">NIS: {{ $selected_siswa->nis }}</p>
                        <p class="mb-0">Kelas: {{ $selected_siswa->kelas->nama_kelas }}</p>
                        <p class="mb-0">Status: {{ $selected_siswa->status }}</p>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <label for="tahun-ajaran">Tampilkan tagihan berdasarkan tahun ajaran</label>
                        <select class="form-control" id="tahun-ajaran" wire:model="selected_tahun">
                            {{-- <option value="" selected disabled>-Pilih Tahun Ajaran-</option> --}}
                            <option value="semua">Semua tahun</option>
                            @foreach ($tahun_ajaran as $ta)
                                <option value="{{ $ta->id }}">{{ $ta->tahun_ajaran }}</option>
                            @endforeach

                        </select>
                    </div>

                    {{-- <div class="col-md-4">
                        <br>
                        <button class="btn btn-primary mt-2" wire:click="showTagihanByTahun()">
                            <i class="fas fa-eye"></i>
                            Tampilkan
                        </button>
                    </div> --}}
                </div>


                <div id="accordion">
                    <label
                        class="font-weight-bold">{{ $selected_tahun == 'semua' ? 'Semua Tagihan' : "Tagihan tahun ajaran $nama_tahun_ajaran->tahun_ajaran" }}</label>

                    @forelse ($tagihan as $tgh)
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

                                                            <span>Dibayar </span>
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
                                                        <button class="btn btn-success btn-sm btn-block" disabled>
                                                            <i class="fas fa-thumbs-up"></i>
                                                            Lunas
                                                        </button>
                                                    @else
                                                        <button class="btn btn-info btn-sm btn-block" @forelse (\Cart::session(Auth()->id())->getContent() as $cart)  @if ($cart->id
                                                            !=$td->id)
                                                            wire:click="showModal({{ $td->id }})"
                                                        @else
                                                            disabled @endif
                                                            @empty
                                                                wire:click="showModal({{ $td->id }})"
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

                                    <span>Dibayar </span>
                                    <strong class="text-success">Rp.{{ number_format($td->total_bayar) }}</strong>
                                    <br>

                                    <span>Sisa </span>
                                    <strong class="text-warning">Rp.{{ number_format($td->sisa) }}</strong>
                                    <br>

                                    <div class="mt-2">
                                        @if ($td->status == 'Lunas')
                                            <button class="btn btn-success btn-sm btn-block" disabled>
                                                <i class="fas fa-thumbs-up"></i>
                                                Lunas
                                            </button>
                                        @else
                                            <button class="btn btn-info btn-sm btn-block" @forelse (\Cart::session(Auth()->id())->getContent() as $cart)  @if ($cart->id !=$td->id)
                                                wire:click="showModal({{ $td->id }})"
                                            @else
                                                disabled @endif
                                                @empty
                                                    wire:click="showModal({{ $td->id }})"
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
        @empty
            <p class="text-danger">Tagihan tidak ditemukan</p>
            @endforelse
            {{-- $tagihan as $tgh --}}
            </div>
            </div>


            <div class="col-md-6">
                {{-- @{{tagihan_id}} --}}
                <div class="card card-info">

                    <div class="card-body">

                        <h6 class="card-title">Pembayaran Detail</h6>
                        {{-- <div v-if="submitCart" class="spinner-border text-danger spinner-border-sm" role="status">
                                <span class="sr-only">Loading...</span>
                            </div> --}}

                        <table class="table table-sm table-hover table-striped m-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pembayaran</th>
                                    <th>Nominal</th>
                                    <th>Dibayar</th>
                                    <th>Sisa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                    $sub_dibayar = 0;
                                @endphp
                                @forelse (\Cart::session(Auth()->id())->getContent() as $cart)
                                    @php
                                        $sub_dibayar += $cart->attributes->dibayar;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <p class="mb-0">{{ $cart->name }}</p>
                                            <p>{{ $cart->attributes->keterangan }}</p>
                                        </td>
                                        <td>
                                            <p>Rp. {{ number_format($cart->price) }}</p>
                                        </td>
                                        <td>
                                            <p>Rp. {{ number_format($cart->attributes->dibayar) }}</p>
                                        </td>
                                        <td>
                                            <p>Rp. {{ number_format($cart->attributes->sisa) }}</p>
                                        </td>
                                        <td>
                                            <button class="btn btn-transparent" wire:click="removeCart({{ $cart->id }})">
                                                <i class="fas fa-times-circle text-danger"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <img alt="image" style="opacity: 0.3" height="90px" width="90px"
                                                src="../img/undraw_empty_cart_co35.png">
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>

                        {{-- \Cart::session(Auth()->id())->getContent() as $cart --}}
                        <div class="py-4 ">
                            <h6>Total Bayar</h6>
                            <h4>Rp. {{ number_format($sub_dibayar) }}</h4>
                        </div>

                        <button class="btn btn- {{ $sub_dibayar == 0 ? 'btn-dark' : 'btn-primary' }} btn-block"
                            wire:click="saveTransaksi({{ \Cart::session(Auth()->id())->getContent() }})"
                            {{ $sub_dibayar == 0 ? 'disabled' : '' }}>Lanjutkan
                            Pembayaran</button>

                    </div>
                </div>
            </div>
            <!-- /.col-md -->

            </div>

        @else
            <div class="col-md-6 mx-auto">
                <img class="w-75" style="opacity: 0.3" src="{{ asset('/img/undraw_file_searching_duff.png') }}" alt="">
            </div>
            @endif
            {{-- ($selected_siswa --}}


            @if ($modal)
                <div class="modal-mask">
                    <div class="modal-wrapper">
                        <div class="modal-dialog shadow" role="document">
                            <div class="modal-content">
                                <div class="modal-body mb-0">
                                    <div class="form-group">
                                        <label for="nama-pembayaran">Nama Pembayaran</label>
                                        <input id="nama-pembayaran" type="text" class="form-control" wire:model="nama_pembayaran"
                                            disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="keterangan">Keterangan</label>
                                        <input id="keterangan" type="text" class="form-control" wire:model="keterangan" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="nominal">Nominal</label>
                                        <input id="nominal" type="text" class="form-control" wire:model="nominal_string" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="dibayar">Dibayar</label>
                                        <input id="dibayar" type="number"
                                            class="form-control{{ $error_message ? ' is-invalid' : '' }}" wire:model="dibayar"
                                            autofocus>
                                        <small class="text-danger">
                                            {{ $error_message ? $error_message : '' }}
                                        </small>
                                    </div>

                                    <button type="button" class="btn btn-danger mt-2 btn-block"
                                        wire:click="closeModal()">Tutup</button>
                                    <button type="button" class="btn btn-primary btn-block" wire:click="addToCart()">
                                        {{-- <span v-if="simpanBtn" class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                                Proses... --}}
                                        Tambahkan ke keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif


            @if ($flash_message)
                <script>
                    $(document).ready(function() {
                        iziToast.success({
                            title: '',
                            message: 'Transaksi Pembayaran Berhasil Disimpan',
                            position: 'bottomCenter'
                        });
                    });

                </script>
            @endif
            </div>
