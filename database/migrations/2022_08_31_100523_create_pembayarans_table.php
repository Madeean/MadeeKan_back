<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembayaransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('bulan');
            $table->string('nama_pengontrak');
            $table->string('nama_kontrakan');
            $table->string('status_lunas')->nullable();
            $table->string('status_konfirmasi');
            $table->string('tanggal_bayar');
            $table->text('bukti_bayar');
            $table->integer('jumlah_bayar');
            $table->string('role')->default('pengontrak');
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
        Schema::dropIfExists('pembayarans');
    }
}
