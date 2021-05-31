@extends('layouts.master')

@section('css')
    <livewire:styles />
@endsection

@section('content')
    <section class="section">
        <!-- Content Header (Page header) -->
        <section class="section-header ">
            <h1>Manajemen Transaksi Pembayaran</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ route('pembayaran.index') }}">Pembayaran</a>
                </div>
                <div class="breadcrumb-item">Transaksi Pembayaran</div>
            </div>
        </section>

        <!-- Main content -->
        <section class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header iseng-sticky bg-white">
                            <a href="{{ route('pembayaran.index') }}" class="btn">
                                <i class="fas fa-arrow-left  text-dark  "></i>
                            </a>
                            <h4 class="ml-3">Form Transaksi Pembayaran</h4>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <livewire:transaksi-pembayaran-livewire />
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>

        </section>
        <!-- /.content -->
    </section>
@endsection
@section('scripts')
    <livewire:scripts />
@endsection
