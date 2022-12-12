<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\AnakKontrakan;
use App\Models\API\Pembayaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Exception;

class pembayaranController extends Controller
{
    public function GetLunasDanGaLunasPemilik(Request $request){
        $data = Pembayaran::with(['user', 'anak_kontrakans'])->where('nama_kontrakan',Auth::user()->nama_kontrakan)->where('status_konfirmasi','Pembayaran Diterima')->get();
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
    public function GetLunasPemilik(Request $request){
        $data = Pembayaran::with(['user', 'anak_kontrakans'])->where('nama_kontrakan',Auth::user()->nama_kontrakan)->where('status_lunas','LUNAS')->where('status_konfirmasi','Pembayaran Diterima')->get();
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


    public function GetBelumLunasPemilik(Request $request){
        $data = Pembayaran::with(['user', 'anak_kontrakans'])->where('user_id',Auth::user()->id)->where('status_Lunas','BELUM LUNAS')->get();
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

        if(Auth::user()->role =="pengontrak"){
            $path = "https://madeekan.madee.my.id/storage/";
            $url = $request->file('bukti_bayar')->store('pembayaran-images', 'public');
            $urel = ''.$path.''.$url;
            $request->bukti_bayar = $urel;

            $data = Pembayaran::create([
                'user_id' => Auth::user()->id,
                'bulan' => $request->bulan,
                'nama_pengontrak' => $request->nama_pengontrak,
                'nama_kontrakan'=>$request->nama_kontrakan,
                'tanggal_bayar' => $request->tanggal_bayar,
                'bukti_bayar' => $request->bukti_bayar,
                'jumlah_bayar' => $request->jumlah_bayar,
                'status_konfirmasi' => 'Menunggu Konfirmasi',
                'role'=>$request->role,
            ]);

            if($data){
                return response()->json([
                    "status" => "success",
                    "message"=>"pembayaran berhasil",
                ],200);
            }else{
                return response()->json([
                    "status" => "error",
                    "message"=>"pembayaran gagal",
                ],404);
            }

        }else if(Auth::user()->role == "pemilik"){
            $path = "https://madeekan.madee.my.id/storage/";
            $url = $request->file('bukti_bayar')->store('pembayaran-images', 'public');
            $urel = ''.$path.''.$url;
            $request->bukti_bayar = $urel;

            $data_pengontrak = User::where('name',$request->nama_pengontrak)->first();

            if($request->jumlah_bayar == $data_pengontrak->harga_kontrakan || $request->jumlah_bayar > $data_pengontrak->harga_kontrakan){
                $data = Pembayaran::create([
                    'user_id' => $data_pengontrak->id,
                    'bulan' => $request->bulan,
                    'nama_pengontrak' => $request->nama_pengontrak,
                    'tanggal_bayar' => $request->tanggal_bayar,
                    'bukti_bayar' => $request->bukti_bayar,
                    'jumlah_bayar' => $request->jumlah_bayar,
                    'status_konfirmasi' => 'Pembayaran Diterima',
                    'status_lunas' => 'LUNAS',
                    'nama_kontrakan'=>$request->nama_kontrakan,
                    'role'=>$request->role,
                ]);
                return response()->json([
                    "status" => "success",
                    "message"=>"pembayaran berhasil",
                ],200);
            }else if($request->jumlah_bayar < $data_pengontrak->harga_kontrakan){
                $data = Pembayaran::create([
                    'user_id' => $data_pengontrak->id,
                    'bulan' => $request->bulan,
                    'nama_pengontrak' => $request->nama_pengontrak,
                    'tanggal_bayar' => $request->tanggal_bayar,
                    'bukti_bayar' => $request->bukti_bayar,
                    'jumlah_bayar' => $request->jumlah_bayar,
                    'status_konfirmasi' => 'Pembayaran Diterima',
                    'status_lunas' => 'BELUM LUNAS',
                    'role'=>$request->role,
                    'nama_kontrakan'=>$request->nama_kontrakan,
                ]);
                return response()->json([
                    "status" => "success",
                    "message"=>"pembayaran berhasil",
                ],200);
            }
            
        }

        
    }

    public function AcceptPembayaran($id){
        
        $data = Pembayaran::where('id',$id)->first();
        $UpdateStatusKonfirmasi = Pembayaran::where('id',$id)->update([
            'status_konfirmasi'=>'Pembayaran Diterima',
        ]);
        $data_user = User::where('name',$data->nama_pengontrak)->first();
        $check = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->whereNotNull('status_lunas')->first();
        // return response()->json([
        //     "status" => "success",
        //     "data"=>$check,
        // ],200);
        
        if($check){
            $harga = $data_user->harga_perbulan;

            $bayar1 = $check->jumlah_bayar;
            $bayar2 = $data->jumlah_bayar;
            $jumlah_bayar = $bayar1 + $bayar2;
            if($harga == $jumlah_bayar || $jumlah_bayar > $harga){
                $data = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->update([
                    'status_lunas'=>'LUNAS',     
                ]);
                $dataa = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->first();
                return response()->json([
                    "status" => "success",
                    "data"=>$dataa,
                ],200);

            }else{
                $bayar1 = $check->jumlah_bayar;
                $bayar2 = $data->jumlah_bayar;
                $bayar = $bayar1 + $bayar2;
                $dataa = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->update([
                    'jumlah_bayar'=>$bayar,
                    'status_lunas'=>'BELUM LUNAS',     
                ]);
                $data = Pembayaran::with(['user','anak_kontrakan'])->where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->first();
                return response()->json([
                    'status' => "success",
                    'data'=>$data,
                ]);
            }
        }

        if($data->jumlah_bayar == $data_user->harga_perbulan || $data->jumlah_bayar > $data_user->harga_perbulan){
            $pembayaran = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->update([
                'status_lunas'=>'LUNAS',     
            ]);
            $dataa = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->first();
            return response()->json([
                "status" => "success",
                "data"=>$dataa,
            ],200);
        }

        if($data->jumlah_bayar < $data_user->harga_perbulan){
            $pembayaran = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->update([
                'status_lunas'=>'BELUM LUNAS',     
            ]);
            $dataa = Pembayaran::where('user_id',$data_user->id)->where('bulan',$data->bulan)->where('nama_pengontrak',$data->nama_pengontrak)->first();
            return response()->json([
                "status" => "success",
                "data"=>$dataa,
            ],200);
        }

    }
    public function RejectPembayaran($id){
        $data = Pembayaran::where('id',$id)->update([
            'status_konfirmasi'=>'Pembayaran Ditolak'
        ]);
        $data2 = Pembayaran::where('id',$id)->first();
        return response()->json([
            'status' => "success",
            'data'=>$data2,
        ]);
    }

    public function detailTransaksi($id){
        $data = Pembayaran::with('user')->where('id',$id)->first();
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
        if(Auth::user()->role == "pengontrak"){
            return response()->json([
                'status' => "Failed",
                'message'=>'anda tidak memiliki akses'
            ]);
        }
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

    public function getRequestPembayaranPemilik(){
        $data = Pembayaran::with('user')->where('status_konfirmasi','Menunggu Konfirmasi')->where('nama_kontrakan',Auth::user()->nama_kontrakan)->get();
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

    public function GetPembayaranPengontrak(){
        $data = Pembayaran::with('user')->where('status_konfirmasi','Pembayaran Diterima')->where('user_id',Auth::user()->id)->get();
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

    public function GetRequestPembayaranPengontrak(){
       
        $data = Pembayaran::with('user')->whereIn('status_konfirmasi',['Menunggu Konfirmasi','pembayaran Ditolak'])->where('user_id',Auth::user()->id)->get();
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

    public function getBelumBayarBulanan($bulan){
        
        if(Auth::user()->role == "pengontrak"){
            return response()->json([
                'status' => "Failed",
                'message'=>'anda tidak memiliki akses'
            ]);
        }
        // $akun = Auth::user()->id;
        
        // return response()->json([
        //     'status' => "success",
        //     'data'=>Auth::user()->nama_kontrakan,
        // ]);

        $data = Pembayaran::where('nama_kontrakan',Auth::user()->nama_kontrakan)->where('bulan',$bulan)->get(['user_id','nama_pengontrak','bukti_bayar','nama_kontrakan','status_konfirmasi','status_lunas']);

        // return response()->json([
        //     'status' => "success",
        //     'message'=>'data ditemukan yang sudah bayar pada bulan '.$bulan,
        //     'data'=>$data,
        // ]);

            if($data->count() == 0){
                $belumBayar = User::where('nama_kontrakan',Auth::user()->nama_kontrakan)->where('role','pengontrak')->get();
                return response()->json([
                    "status" => "success",
                    "message"=>"semua anak kontrakan belum bayar bulan $bulan, list belum bayar",
                    "data"=>$belumBayar,
                ],200);
            }else{
                $dataSudahBayar = [];
                for($i = 0; $i<$data->count(); $i++){
                    $dataSudahBayar[$i] = $data[$i]->user_id;
                }
                $belumBayar = User::whereNotIn('id',$dataSudahBayar)->where('role','pengontrak')->where('nama_kontrakan',Auth::user()->nama_kontrakan)->get();
                return response()->json([
                    "status" => "success",
                    "list_data_sudah_bayar"=>$dataSudahBayar,
                    "data_yang_sudah_bayar"=>$data,
                    "data_yang_belum_bayar"=>$belumBayar,
                ],200);

            }
    }

    public function bulan(){
        $data  = User::whereMonth('created','<','12')->get();
        return response()->json([
            "status" => "success",
            "data"=>$data,
        ],200);
    }

    public function getPembayaranDiterimaPemilik(){
        $data = Pembayaran::with('user')->where('nama_kontrakan',Auth::user()->nama_kontrakan)->where('status_konfirmasi','Pembayaran Diterima')->get();
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

    public function getPembayaranBelumLunasPemilik(){
        $data = Pembayaran::with('user')->where('nama_kontrakan',Auth::user()->nama_kontrakan)->where('status_lunas','BELUM LUNAS')->get();
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
    
    
    public function getJumlahOrangNgontrak(){
        $data = User::where('nama_kontrakan',Auth::user()->nama_kontrakan)->where('role','pengontrak')->count();
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
    
    
    
}