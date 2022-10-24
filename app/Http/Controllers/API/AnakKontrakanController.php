<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\API\AnakKontrakan;
use App\Http\Controllers\Controller;
use App\Models\API\Pembayaran;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AnakKontrakanController extends Controller
{
    public function addAnakKontrakan(Request $request){
        $validator = Validator::make($request->all(), [
            'name'     => 'required|unique:anak_kontrakans',
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

        $path = "https://madeekan.madee.my.id/storage/";
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
            'created'=>date('Y-m-d'),
            'updated'=>date('Y-m-d'),
        ]);
        
        return response()->json([
            "status" => "success",
            "data"=>$data,
        ],200);
        
        
    }

    public function editAnakKontrakan(Request $request, $id){
        $img ="";
        $data = AnakKontrakan::with('user')->where('id',$id)->first();
        $validator = Validator::make($request->all(), [
            'name'     => 'required|unique:anak_kontrakans,name,'.$data->name,
            'umur'  => 'required|integer',
            'alamat_asli'=>'required',
            'foto_muka'=>'image|max:2048',
            'alamat_kontrakan'=>'required',
            'harga_perbulan'=>'required',
        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if($data->user_id != Auth::user()->id){
            return response()->json([
                "status" => "error",
                "message"=>"anda tidak memiliki akses",
            ]);
        }

        if($request->file('image')){
            if(File::exists($data->foto_muka)){
                File::delete($data->foto_muka);
            }
            
            $path = "https://madeekan.madee.my.id/storage/";
            $url = $request->file('foto_muka')->store('anak-kontrakan-images', 'public');
            $urel = ''.$path.''.$url;
            $img = $urel;
            
            $anak = $data->update([
                'name' => $request->name,
                'foto_muka' => $img,
                'umur' => $request->umur,
                'alamat_asli' => $request->alamat_asli,
                'alamat_kontrakan' => $request->alamat_kontrakan,
                'harga_perbulan' => $request->harga_perbulan,
                'updated' => date('Y-m-d'),

            ]);

            return response()->json([
                'status'=>"success",
                'message' => 'berhasil edit anak kontrakan',
                'data'=>$anak,
            ]);

        }
        $anak = $data->update([
            'name' => $request->name,
            'umur' => $request->umur,
            'alamat_asli' => $request->alamat_asli,
            'alamat_kontrakan' => $request->alamat_kontrakan,
            'harga_perbulan' => $request->harga_perbulan,
            'updated' => date('Y-m-d'),
        ]);
        return response()->json([
            'status'=>"success",
            'message' => 'berhasil edit anak kontrakan',
            'data'=>$anak,
        ]);

    }

    public function getAnakKontrakan(){
        $data = AnakKontrakan::where('user_id',Auth::user()->id)->get();
        if($data){
            return response()->json([
                "status" => "success",
                "data"=>$data,
            ],200);
        }else{
            return response()->json([
                "status" => "error",
                "message"=>"data tidak ditemukan",
            ],404);
        }
    }

    public function detailAnakKontrakan($id){
        $data = AnakKontrakan::where('id',$id)->first();
        if($data){
            return response()->json([
                "status" => "success",
                "data"=>$data,
            ],200);
        }else{
            return response()->json([
                "status" => "error",
                "message"=>"data tidak ditemukan",
            ],404);
        }
    }

    public function deleteAnakKontrakan($id){
        try{
            $anakKontrakan = AnakKontrakan::where('id',$id)->first();
            if(File::exists($anakKontrakan->foto_muka)) {
                File::delete($anakKontrakan->foto_muka);
            }
            AnakKontrakan::destroy($id);
            return response()->json([
                "status" => "success",
                "message"=>"data berhasil dihapus",
            ],200);
        }catch(Exception $err){
            return response()->json([
                "status" => "error",
                "message"=>"data gagal dihapus ".$err->getMessage(),
            ],500);
        }
        
    }

    public function getBelumBayarBulanIni($bulan){
        try{
            
            $data = Pembayaran::where('user_id',Auth::user()->id)->where('bulan',$bulan)->get(['anak_kontrakan_id','user_id','nama_pengontrak','bukti_bayar']);
            if($data->count() == 0){
                $belumBayar = AnakKontrakan::where('user_id',Auth::user()->id)->get();
                return response()->json([
                    "status" => "success",
                    "message"=>"semua anak kontrakan belum bayar bulan $bulan",
                    "data"=>$belumBayar,
                ],200);
            }else{
                $dataSudahBayar = [];
                for($i = 0; $i<$data->count(); $i++){
                    $dataSudahBayar[$i] = $data[$i]->anak_kontrakan_id;
                }
                $belumBayar = AnakKontrakan::where('user_id',Auth::user()->id)->whereNotIn('id',$dataSudahBayar)->get();

                return response()->json([
                    "status" => "success",
                    "data yang sudah bayar"=>$data,
                    "data yang belum bayar"=>$belumBayar,
                ],200);
            }

        }catch(Exception $err){
            return response()->json([
                "status" => "error",
                "message"=>"data gagal dihapus ".$err->getMessage(),
            ],500);
        }
    }


}