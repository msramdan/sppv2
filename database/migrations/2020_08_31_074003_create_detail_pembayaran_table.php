<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailPembayaranTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_pembayaran_id')->constrained('transaksi_pembayaran')->onDelete('cascade');
            $table->foreignId('tagihan_details_id')->constrained('tagihan_details')->onDelete('cascade');
            $table->string('nama_pembayaran');
            $table->string('keterangan');
            $table->integer('harga');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_pembayaran');
    }
}
