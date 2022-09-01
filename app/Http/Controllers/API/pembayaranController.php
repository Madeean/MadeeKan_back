<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\AnakKontrakan;
use App\Models\API\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class pembayaranController extends Controller
{
    public function GetLunas(Request $request){
        $data = Pembayaran::with(['user, anak_kontakan'])->where('user_id',Auth::user()->id)->where('status','LUNAS')->get();
        if($data){

            return response()->json([
                "status" => "success",
                "data"=>$data,
            ],200);
        }else{
            return response()->json([
                "status" => "success",
                "message"=>"pembayaran belum lunas semua",
            ]);
        }
    }

    public function GetBelumLunas(Request $request){
        $data = Pembayaran::with(['user, anak_kontakan'])->where('user_id',Auth::user()->id)->where('status','BELUM LUNAS')->get();
        if($data){
            return response()->json([
                "status" => "success",
                "data"=>$data,
            ],200);
        }else{
            return response()->json([
                "status" => "success",
                "data"=>"tidak ada yang belum lunas"
            ]);
        }
    }

    public function bayar(Request $request){
        $validator = Validator::make($request->all(), [
            'bulan'     => 'required',
            'nama_pengontrak'  => 'required',
            'tanggal_bayar'=>'required',
            'bukti_bayar'=>'required',
            'jumlah_bayar'=>'required',
            'anak_kontrakan_id'=>'required',
        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $anak_kontrakan = AnakKontrakan::where('id',$request->anak_kontrakan_id)->first();

        $check = Pembayaran::where('user_id',Auth::user()->id)->where('bulan',$request->bulan)->where('nama_pengontrak',$request->nama_pengontrak)->first();

        if($check){
            Pembayaran::update([
                'status'=>'LUNAS'
            ]);
            $data = Pembayaran::with(['user','anak_kontrakan'])->where('user_id',Auth::user()->id)->where('bulan',$request->bulan)->where('nama_pengontrak',$request->nama_pengontrak)->first();
            return response()->json([
                'success' => true,
                'data'=>$data,
            ]);
        }
        if($request->jumlah_bayar == $anak_kontrakan->harga_perbulan){
            $data = Pembayaran::create([
                'user_id'=>Auth::user()->id,
                'anak_kontrakan_id'=>$request->anak_kontrakan_id,
                'bulan'=>$request->bulan,
                'nama_pengontrak'=>$request->nama_pengontrak,
                'tanggal_bayar'=>$request->tanggal_bayar,
                'bukti_bayar'=>$request->bukti_bayar,
                'jumlah_bayar'=>$request->jumlah_bayar,
                'status'=>'LUNAS'
            ]);
            return response()->json([
                'success' => true,
                'data'=>$data,
            ]);
        }


        $data = Pembayaran::create([
            'user_id'=>Auth::user()->id,
            'anak_kontrakan_id'=>$request->anak_kontrakan_id,
            'bulan'=>$request->bulan,
            'nama_pengontrak'=>$request->nama_pengontrak,
            'tanggal_bayar'=>$request->tanggal_bayar,
            'bukti_bayar'=>$request->bukti_bayar,
            'jumlah_bayar'=>$request->jumlah_bayar,
            'status'=>'BELUM LUNAS'
        ]);

        return response()->json([
            'success' => true,
            'data'=>$data,
        ]);
    }

    public function detailTransaksi($id){
        $data = Pembayaran::with(['user','anak_kontrakan'])->where('id',$id)->first();
        if($data){
            return response()->json([
                'success' => true,
                'data'=>$data,
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message'=>'data tidak ditemukan'
            ]);
        }
    }
}
