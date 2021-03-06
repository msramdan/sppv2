@extends('layouts.master')

@section('content')
    <section class="section">
        <!-- Content Header (Page header) -->
        <section class="section-header ">
            <h1>Manajemen Pembayaran</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Transaksi Pembayaran</div>
            </div>
        </section>

        <!-- Main content -->
        <section class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header iseng-sticky bg-white">
                            <h4>Transaksi Pembayaran</h4>
                            <div class="card-header-action">
                                <a href="{{ route('pembayaran.transaksi') }}" class="btn btn-primary btn-icon"
                                    data-toggle="tooltip" data-placement="top" title="" data-original-title="Tambah Data">
                                    <i class="fas fa-plus-circle px-2"></i>
                                </a>

                                {{-- <a href="{{ route('pembayaran.import.view') }}" class="btn btn-secondary btn-icon ml-2"
                                    title="Import Excel New" data-toggle="tooltip" data-placement="top"
                                    data-original-title="Import Data Pembayaran">
                                    <i class="fas fa-file-import px-2"></i>
                                </a> --}}

                                <a href="#" class="btn btn-secondary btn-icon ml-2" onclick="handleImport()"
                                    title="Import Excel" data-toggle="tooltip" data-placement="top"
                                    data-original-title="Import Data Pembayaran">
                                    <i class="fas fa-file-import px-2"></i>
                                </a>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">

                            <form action="{{ route('pembayaran.index') }}" method="GET">
                                {{-- @csrf --}}
                                <div class="input-group input-group mb-3 float-right" style="width: 300px;">
                                    <input type="text" name="keyword" class="form-control float-right" placeholder="Search"
                                        value="{{ request()->query('keyword') }}">


                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-light"><i class="fas fa-search"></i></button>
                                    </div>
                                    <div class="input-group-append">
                                        <a href="{{ route('pembayaran.index') }}" title="Refresh" class="btn btn-light"><i
                                                class="fas fa-circle-notch mt-2"></i></a>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-head-fixed text-nowrap table-bordered">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Tgl.Pembayaran</th>
                                            <th>Kode</th>
                                            <th>NIS & Nama</th>
                                            <th>Metode <br> Pembayaran</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pembayaran as $row)
                                            <tr>
                                                <td style="width: 50px">{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ date('d M Y H:i', strtotime($row->tanggal_bayar)) }}
                                                </td>
                                                <td>
                                                    <a class="" href="{{ route('pembayaran.show', $row->id) }}">
                                                        {{ $row->kode_pembayaran }}
                                                    </a>
                                                </td>
                                                <td>
                                                    {{ @$row->siswa->nis }} -
                                                    {{ @$row->siswa->kelas->nama_kelas }}<br>
                                                    <strong>{{ @$row->siswa->nama_lengkap }}</strong> <br>
                                                </td>
                                                <td>{{ $row->metode_pembayaran }}</td>
                                                <td class="text-right">Rp.{{ number_format($row->total) }}</td>
                                                <td>
                                                    @if ($row->status == 'settlement' || $row->status == 'Success')
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle "></i>
                                                            Settlement
                                                        </span>
                                                    @endif
                                                    @if ($row->status == 'expire')
                                                        <span class="text-danger">
                                                            <i class="fas fa-times-circle "></i>
                                                            Expire
                                                        </span>
                                                    @endif
                                                    @if ($row->status == 'pending')
                                                        <span class="text-warning">
                                                            <i class="fas fa-info-circle "></i>
                                                            Pending
                                                        </span>
                                                    @endif
                                                    @if ($row->status === 'cancel')
                                                        <span class="text-secondary">
                                                            <i class="fas fa-info-circle "></i>
                                                            Cancel
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Data Tidak Ada</td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                                {{ $pembayaran->appends(['keyword' => request()->query('keyword')])->links() }}
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">

                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>

        </section>
        <!-- /.content -->
    </section>


    <!-- Modal Import File-->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Import Data Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('pembayaran.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        {{-- <div class="form-group">
                            <label for="jenis">Pilih Jenis Pembayaran</label>
                            <select name="jenis" id="jenis" required class="form-control @error('jenis') is-invalid @enderror">
                                        <option value="">-Pilih-</option>
                                        <option value="bulanan">SPP (Bulanan)</option>
                                        <option value="bebas">KBM (Angsuran/Bebas)</option>
                                    </select>
                            @error('jenis')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div> --}}

                        <div class="form-group">
                            <label for="import_pembayaran">Import File</label>
                            <input type="file" class="form-control-file" name="import_pembayaran" id="import_pembayaran"
                                placeholder="" aria-describedby="fileHelpId" required>

                            @error('import_pembayaran')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <small id="fileHelpId" class="form-text text-muted">Tipe file : xls, xlsx</small>

                            <small id="fileHelpId" class="form-text text-muted">Pastikan file upload sesuai format. <br> <a
                                    href="{{ url('template/contoh_format_pembayaran.xlsx') }}">Download
                                    contoh format file xlsx <i class="fas fa-download ml-1   "></i></a></small>
                        </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete-->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Data Kelas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mt-3">Apakah kamu yakin menghapus Data Kelas ?</p>
                </div>
                <div class="modal-footer">
                    <form action="" method="POST" id="deleteForm">
                        @method('DELETE')
                        @csrf
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak, Kembali</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function handleDelete(id) {
        let form = document.getElementById('deleteForm')
        form.action = `./kelas/${id}`
        console.log(form)
        $('#deleteModal').modal('show')
    }

    function handleImport(e) {
        $('#importModal').modal('show')
    }

</script>

@if (session()->has('success'))
    <script>
        $(document).ready(function() {
            iziToast.success({
                title: '',
                message: '{{ session()->get('success') }}',
                position: 'bottomCenter'
            });
        });

    </script>
@endif

@if (session()->has('error'))
    <script>
        $(document).ready(function() {
            iziToast.error({
                title: '',
                message: '{{ session()->get('error') }}',
                position: 'bottomCenter'
            });
        });

    </script>
@endif

@endsection
