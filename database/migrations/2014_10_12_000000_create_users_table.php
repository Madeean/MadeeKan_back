<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->integer('rooms')->nullable();
            $table->string('nama_kontrakan')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('role');
            $table->string('alamat_sesuai_ktp')->nullable();
            $table->string('alamat_kontrakan_sekarang')->nullable();
            $table->string('harga_perbulan')->nullable();
            $table->text('foto_muka')->nullable();
            $table->integer('umur')->nullable();

            $table->string('password');
            $table->string('created');
            $table->string('updated');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
