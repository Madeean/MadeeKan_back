<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnakKontrakan extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'umur',
        'alamat_asli',
        'foto_muka',
        'alamat_kontrakan',
        'harga_perbulan',
        'user_id',
        'created',
        'updated',
    ];

    public function user(){
        return $this->belongsTo(User::class,'id','user_id');
    }
}
