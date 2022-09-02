<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnakKontrakansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anak_kontrakans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('umur');
            $table->text('alamat_asli');
            $table->text('alamat_kontrakan');
            $table->integer('harga_perbulan');
            $table->text('foto_muka');
            $table->integer('user_id');
            $table->string('created');
            $table->string('updated');
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
        Schema::dropIfExists('anak_kontrakans');
    }
}
