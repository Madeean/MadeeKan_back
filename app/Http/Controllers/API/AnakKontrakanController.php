<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\API\AnakKontrakan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AnakKontrakanController extends Controller
{
    public function addAnakKontrakan(Request $request){
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'umur'  => 'required|integer',
            'alamat_asli'=>'required',
            'foto_muka'=>'required|image|max:2048',
            'alamat_kontrakan'=>'required',
            'harga_perbulan'=>'required',
        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $path = "http://127.0.0.1:8000/storage/";
        $url = $request->file('foto_muka')->store('anak-kontrakan-images', 'public');
        $urel = ''.$path.''.$url;
        $request->foto_muka = $urel;

        $check = AnakKontrakan::where('name',$request->name)->first();
        if($check){
            return response()->json([
                "status" => "error",
                "message"=>"nama anak kontrakan sudah ada",
            ]);
        }
        $data = AnakKontrakan::create([
            'name'=>$request->name,
            'umur'=>$request->umur,
            'alamat_asli'=>$request->alamat_asli,
            'foto_muka'=>$request->foto_muka,
            'alamat_kontrakan'=>$request->alamat_kontrakan,
            'harga_perbulan'=>$request->harga_perbulan,
            'user_id'=>Auth::user()->id,
        ]);
        
        return response()->json([
            "status" => "success",
            "data"=>$data,
        ],200);
        
        
    }
}
