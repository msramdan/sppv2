@extends('layouts.master')

@section('content')
    <section class="section">
        <!-- Content Header (Page header) -->
        <section class="section-header ">
            <h1>Manajemen Jenis Pembayaran</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('jenispembayaran.index') }}">Jenis Pembayaran</a>
                </div>
                <div class="breadcrumb-item">Edit Jenis Pembayaran</div>
            </div>
        </section>

        <!-- Main content -->
        <section class="section-body">

            <div class="row">
                <div class="col-12">
                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-has-icon mb-4 alert-dismissible show fade">
                            <button class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                            <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
                            <div class="alert-body">
                                <div class="alert-title">Error</div>
                                {{ session()->get('error') }}
                            </div>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header iseng-sticky bg-white">
                            <a href="{{ route('jenispembayaran.index') }}" class="btn">
                                <i class="fas fa-arrow-left  text-dark  "></i>
                            </a>
                            <h4 class="ml-3">Form Edit Jenis Pembayaran</h4>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form method="POST" action="{{ route('jenispembayaran.update', $jenis_pembayaran->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="hidden" name="old_tipe" value="{{ $jenis_pembayaran->tipe }}">

                                        <div class="form-group">
                                            <label for="nama_pembayaran">Nama Pembayaran</label>
                                            <input type="text" name="nama_pembayaran"
                                                class="form-control @error('nama_pembayaran') is-invalid @enderror"
                                                id="nama_pembayaran" value="{{ $jenis_pembayaran->nama_pembayaran }}"
                                                placeholder="Contoh SPP, DSP, Sumbangan apalah" autofocus>
                                            @error('nama_pembayaran')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="tahunajaran_id">Tahun Pelajaran</label>
                                            <select name="tahunajaran_id" id="tahunajaran_id"
                                                class="form-control @error('tahunajaran_id') is-invalid @enderror">
                                                <option value="">-Pilih Tahun Pelajaran-</option>
                                                @foreach ($tahun_ajaran as $item)
                                                    <option value="{{ $item->id }}" @if ($item->id == $jenis_pembayaran->tahunajaran_id) selected @endif>

                                                        {{ $item->tahun_ajaran }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tahunajaran_id')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="">Semester</label><br>
                                            <input type="radio" id="satu" value="1" name="semester" {{ ($jenis_pembayaran->semester == 1) ? "checked" : ''}}>
                                            <label for="satu">Ganjil</label>

                                            <input type="radio" id="dua" value="2" name="semester" {{ ($jenis_pembayaran->semester == 2) ? "checked" : ''}} >
                                            <label for="dua">Genap</label>
                                            @error('semester')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>


                                        <div class="form-group">
                                            <label for="tipe">Tipe Pembayaran</label>
                                            <select name="tipe" id="tipe"
                                                class="form-control @error('tipe') is-invalid @enderror">
                                                <option value="">-Pilih Tipe Pembayaran-</option>
                                                <option value="bulanan"
                                                    {{ 'bulanan' === $jenis_pembayaran->tipe ? 'selected' : '' }}>Setiap
                                                    Bulan</option>
                                                <option value="bebas"
                                                    {{ 'bebas' === $jenis_pembayaran->tipe ? 'selected' : '' }}>
                                                    Bebas/Angsuran</option>
                                            </select>
                                            @error('tipe')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="harga">Biaya/Nominal</label>
                                            <input type="number" name="harga"
                                                class="form-control @error('harga') is-invalid @enderror" id="harga"
                                                value="{{ $jenis_pembayaran->harga }}"
                                                placeholder="Masukkan Biaya atau jumlah Nominal" autofocus>
                                            @error('harga')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group d-flex justify-content-end">
                                            <a class="btn btn-light "
                                                href="{{ route('jenispembayaran.index') }}">Batal</a>

                                            <button type="submit" class="btn btn-primary ml-2">
                                                Update
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="semua_kelas" class="text-dark">Pembayaran Untuk</label>

                                        <br>

                                        <label class="text-dark">Per Kelas</label>

                                        {{-- per kelas --}}
                                        <div class="accordion">
                                            <div class="accordion-header" role="button" data-toggle="collapse"
                                                data-target="#panel-body-kelas">
                                                <h4>Kelas</h4>
                                            </div>

                                            <div class="accordion-body collapse" id="panel-body-kelas"
                                                data-parent="#accordion">

                                                <div class="form-group mb-0">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="semua_kelas" value="semua"
                                                            {{ $pembayaran_untuk[0] == 'semua kelas' ? 'checked' : '' }}
                                                            class="custom-control-input" id="one">
                                                        <label class="custom-control-label" for="one">Semua Kelas</label>
                                                    </div>

                                                    <div class="form-group">
                                                        @php
                                                            $checked_semua_siswa_kelas = [];
                                                        @endphp

                                                        @foreach ($kelas as $index => $kls)
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" name="semua_siswa_kelas[]"
                                                                    value="{{ $kls->id }}"
                                                                    class="custom-control-input kelas-choice"
                                                                    id="customCheck{{ $kls->id + 99 }}" @if ($pembayaran_untuk[0] == 'semua kelas') checked
                                                                    @elseif($pembayaran_untuk[0] == 'semua siswa kelas')





                                                                    @foreach ($pembayaran_untuk[1] as $pu)
                                                                @if ($pu == $kls->id)
                                                                    checked
                                                                    @php
                                                                        $checked_semua_siswa_kelas[] = $kls->id;
                                                                    @endphp @endif
                                                                @endforeach
                                                        @endif>

                                                        <label class="custom-control-label"
                                                            for="customCheck{{ $kls->id + 99 }}">Semua
                                                            Siswa
                                                            {{ $kls->nama_kelas }}</label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- per siswa --}}
                                    <div id="accordion">
                                        <label class="text-dark">Per siswa</label>

                                        @foreach ($kelas as $kls)
                                            <div class="accordion">
                                                <div class="accordion-header" role="button" data-toggle="collapse"
                                                    data-target="#panel-body-{{ $kls->id }}">
                                                    <h4>{{ $kls->nama_kelas }}</h4>
                                                </div>

                                                <div class="accordion-body accordion-body-custom collapse"
                                                    id="panel-body-{{ $kls->id }}" data-parent="#accordion">

                                                    @foreach ($kls->siswa as $index => $sws)
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="per_siswa[]"
                                                                value="{{ $sws->id }}"
                                                                class="custom-control-input per-murid-choice"
                                                                id="customCheck{{ $sws->id }}" @if ($pembayaran_untuk[0] == 'semua kelas') checked
@elseif($pembayaran_untuk[0] == 'per siswa')




                                                                          @foreach ($pembayaran_untuk[1] as $index=> $pu)

                                                            @if ($pu == $sws->id)
                                                                checked @endif
                                                            @endforeach
                                                    @endif
                                                    @foreach ($checked_semua_siswa_kelas as $cssk)
                                                        {{ $cssk == $sws->kelas_id ? 'checked' : '' }}
                                                    @endforeach
                                                    >
                                                    <label class="custom-control-label"
                                                        for="customCheck{{ $sws->id }}">{{ $sws->nis . ' - ' . $sws->nama_lengkap }}</label>
                                                </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                        </div>
                    </div>
                </div>
                </form>
            </div>
            </div>
            <!-- /.card -->
            </div>
            </div>

        </section>
        <!-- /.content -->
    </section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#tipe').select2()
        $('#tahunajaran_id').select2()
        $("#one").click(function() {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

        $(".per-murid-choice").change(function() {
            if (this.checked) {
                $('.kelas-choice').prop('checked', false);
            }
        });

        $(".kelas-choice").change(function() {
            if (this.checked) {
                $('.per-murid-choice').prop('checked', false);
            }
        });
    });

</script>
@if (session()->has('success'))
    <script>
        $(document).ready(function() {
            // toastr["success"]('{{ session()->get('success') }}')
            iziToast.success({
                title: '',
                message: '{{ session()->get('success') }}',
                position: 'bottomCenter'
            });
        });

    </script>
@endif
@endsection
