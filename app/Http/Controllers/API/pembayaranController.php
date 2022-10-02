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
    public function GetLunasDanGaLunas(Request $request){
        $data = Pembayaran::with(['user', 'anak_kontrakans'])->where('user_id',Auth::user()->id)->get();
        if($data){

            return response()->json([
                "status" => "success",
                "data"=>$data,
            ],200);
        }else{
            return response()->json([
                "status" => "success",
                "message"=>"pembayaran belum ada",
            ],404);
        }
    }
    public function GetLunas(Request $request){
        $data = Pembayaran::with(['user', 'anak_kontrakans'])->where('user_id',Auth::user()->id)->where('status','LUNAS')->get();
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
        $data = Pembayaran::with(['user', 'anak_kontrakans'])->where('user_id',Auth::user()->id)->where('status','BELUM LUNAS')->get();
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
            'bukti_bayar'=>'required|image|max:2048',
            'jumlah_bayar'=>'required',
        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $path = "https://madeekan.madee.my.id/storage/";
        $url = $request->file('bukti_bayar')->store('pembayaran-images', 'public');
        $urel = ''.$path.''.$url;
        $request->bukti_bayar = $urel;

        $anak_kontrakan = AnakKontrakan::where('name',$request->nama_pengontrak)->first();

        $check = Pembayaran::where('user_id',Auth::user()->id)->where('bulan',$request->bulan)->where('nama_pengontrak',$request->nama_pengontrak)->first();

        if($check){
            $harga = $anak_kontrakan->harga_perbulan;

            $bayar1 = $check->jumlah_bayar;
            $bayar2 = $request->jumlah_bayar;
            $jumlah_bayar = $bayar1 + $bayar2;
            if($harga == $jumlah_bayar || $jumlah_bayar > $harga){
                $data = Pembayaran::where('user_id',Auth::user()->id)->where('bulan',$request->bulan)->where('nama_pengontrak',$request->nama_pengontrak)->update([
                    'status'=>'LUNAS',     
                ]);
                $dataa = Pembayaran::where('user_id',Auth::user()->id)->where('bulan',$request->bulan)->where('nama_pengontrak',$request->nama_pengontrak)->first();
                return response()->json([
                    "status" => "success",
                    "data"=>$dataa,
                ],200);

            }else{
                $bayar1 = $check->jumlah_bayar;
                $bayar2 = $request->jumlah_bayar;
                $bayar = $bayar1 + $bayar2;
                $dataa = Pembayaran::where('user_id',Auth::user()->id)->where('bulan',$request->bulan)->where('nama_pengontrak',$request->nama_pengontrak)->update([
                    'jumlah_bayar'=>$bayar,     
                ]);
                $data = Pembayaran::with(['user','anak_kontrakan'])->where('user_id',Auth::user()->id)->where('bulan',$request->bulan)->where('nama_pengontrak',$request->nama_pengontrak)->first();
                return response()->json([
                    'status' => "success",
                    'data'=>$data,
                ]);
            }
            
        }
        if($request->jumlah_bayar == $anak_kontrakan->harga_perbulan || $request->jumlah_bayar > $anak_kontrakan->harga_perbulan){
            $data = Pembayaran::create([
                'user_id'=>Auth::user()->id,
                'anak_kontrakan_id'=>$anak_kontrakan->id,
                'bulan'=>$request->bulan,
                'nama_pengontrak'=>$request->nama_pengontrak,
                'tanggal_bayar'=>$request->tanggal_bayar,
                'bukti_bayar'=>$request->bukti_bayar,
                'jumlah_bayar'=>$request->jumlah_bayar,
                'status'=>'LUNAS'
            ]);
            return response()->json([
                'status' => "success",
                'data'=>$data,
            ]);
        }


        $data = Pembayaran::create([
            'user_id'=>Auth::user()->id,
            'anak_kontrakan_id'=>$anak_kontrakan->id,
            'bulan'=>$request->bulan,
            'nama_pengontrak'=>$request->nama_pengontrak,
            'tanggal_bayar'=>$request->tanggal_bayar,
            'bukti_bayar'=>$request->bukti_bayar,
            'jumlah_bayar'=>$request->jumlah_bayar,
            'status'=>'BELUM LUNAS'
        ]);

        return response()->json([
            'status' => "success",
            'data'=>$data,
        ]);
    }

    public function detailTransaksi($id){
        $data = Pembayaran::with(['user','anak_kontrakan'])->where('id',$id)->first();
        if($data){
            return response()->json([
                'status' => "success",
                'data'=>$data,
            ]);
        }else{
            return response()->json([
                'status' => "Failed",
                'message'=>'data tidak ditemukan'
            ]);
        }
    }

    public function deleteTransaksi($id){
        $data = Pembayaran::where('id',$id)->delete();
        if($data){
            return response()->json([
                'status' => "success",
                'message'=>'data berhasil dihapus'
            ]);
        }else{
            return response()->json([
                'status' => "success",
                'message'=>'data tidak ditemukan'
            ]);
        }
    }
}