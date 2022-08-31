<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
    public function anak_kontrakans(){
        return $this->hasOne(AnakKontrakan::class,'id','anak_kontrakan_id');
    }
}
