<?php

namespace App\Models\API;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
