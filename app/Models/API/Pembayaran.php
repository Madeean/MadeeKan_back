<?php

namespace App\Models\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bulan',
        'nama_pengontrak',
        'tanggal_bayar',
        'status_lunas',
        'status_konfirmasi',
        'nama_kontrakan',
        'bukti_bayar',
        'jumlah_bayar',
        'role',

    ];

    public function user(){
        return $this->hasMany(User::class,'id','user_id');
    }
    public function anak_kontrakans(){
        return $this->hasMany(AnakKontrakan::class,'id','anak_kontrakan_id');
    }
}
