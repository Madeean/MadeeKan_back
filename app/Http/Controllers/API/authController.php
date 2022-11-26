<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;

use App\Models\API\Pembayaran;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class authController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required',
            'rooms'=>'numeric',
            'nama_kontrakan'=>'string',
            'role'=>'string|required',

        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        

        //find user by email from request "email"
        $user = User::where('email', $request->email)->first();

        //if password from user and password from request not same
        if ($user) {
            
            //return with status code "400" and login failed
            return response()->json([
                'status' => "error",
                'message' => 'register Failed!',
            ], 400);
        }

        if($request->role == "pemilik"){
            User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'rooms'=>$request->rooms,
                'role'=>$request->role,
                'nama_kontrakan'=>$request->nama_kontrakan,
                'harga_perbulan'=>$request->harga_perbulan,
                'created'=>date('Y-m-d'),
                'updated'=>date('Y-m-d'),
    
            ]);
            $user = User::where('email', $request->email)->first();
    
            //user success login and create token
            return response()->json([
                'status' => "success",
                'message' => 'Register Successfully!',
                'user'    => $user,
                'token'   => $user->createToken('authToken')->accessToken    
            ], 200);
        }else if($request->role == "pengontrak"){

            $path = "https://madeekan.madee.my.id/storage/";
            $url = $request->file('foto_muka')->store('user-role-pengontrak', 'public');
            $urel = ''.$path.''.$url;
            $request->foto_muka = $urel;
            User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
                'role'=>$request->role,
                'foto_muka'=>$request->foto_muka,
                'alamat_sesuai_ktp'=>$request->alamat_sesuai_ktp,
                'alamat_kontrakan_sekarang'=>$request->alamat_kontrakan_sekarang,
                'harga_perbulan'=>$request->harga_perbulan,
                'nama_kontrakan'=>$request->nama_kontrakan,
                'umur'=>$request->umur,
                'created'=>date('Y-m-d'),
                'updated'=>date('Y-m-d'),
    
            ]);
            $user = User::where('email', $request->email)->first();
    
            //user success login and create token
            return response()->json([
                'status' => "success",
                'message' => 'Register Successfully!',
                'user'    => $user,
                'token'   => $user->createToken('authToken')->accessToken    
            ], 200);
        }else{
            return response()->json([
                'status' => "error",
                'message' => 'register Failed!',
            ], 400);
        }

        
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required',

        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find user by email from request "email"
        $user = User::where('email', $request->email)->first();

        //if password from user and password from request not same
        if (!$user || !Hash::check($request->password, $user->password)) {
            
            //return with status code "400" and login failed
            return response()->json([
                'success' => "error",
                'message' => 'Login Failed!',
            ], 400);
        }

        //user success login and create token
        return response()->json([
            'status' => "success",
            'message' => 'Login Successfully!',
            'user'    => $user,
            'token'   => $user->createToken('authToken')->accessToken    
        ], 200);
    }

    public function logout(Request $request){
        $removeToken = $request->user()->tokens()->delete();

        //if remove token successfully
        if($removeToken) {
            return response()->json([
                'status' => "success",
                'message' => 'Logout Successfully!',  
            ]);
        }
    }

    public function getUser($id){
        $user = User::where('id', $id)->first();
        if($user){
            return response()->json([
                'status' => "success",
                'message' => 'Get User Successfully!',  
                'user' => $user
            ]);
        }else{
            return response()->json([
                'status' => "error",
                'message' => 'Get User Failed!',  
            ]);
        }

    }

    public function editProfile(Request $request, $id){
        $data = User::where('id',$id)->first();
        $validator = Validator::make($request->all(), [
            'email'     => 'email|unique:users,email,'.$data->email,
            'rooms'=>'integer',
            'name'=>'string',
            'umur'=>'integer',
            'password'=>'min:3',

        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if(Auth::user()->role == "pemilik"){
            User::where('id', $id)->update([
                'name'=>($request->name )? $request->name : $data->name,
                'email'=>($request->email )? $request->email : $data->email,
                'rooms'=>($request->rooms )? $request->rooms : $data->rooms,
                'nama_kontrakan'=>($request->nama_kontrakan )? $request->nama_kontrakan : $data->nama_kontrakan,

                'updated'=>date('Y-m-d'),
    
            ]);
            $user = User::where('id', $id)->first();

            if($request->nama_kontrakan){
                $data1 = User::where('nama_kontrakan',$data->nama_kontrakan)->update([
                    'nama_kontrakan'=>($request->nama_kontrakan )? $request->nama_kontrakan : $data->nama_kontrakan,
                ]);
                $data2 = Pembayaran::where('nama_kontrakan',$data->nama_kontrakan)->update([
                    'nama_kontrakan'=>($request->nama_kontrakan )? $request->nama_kontrakan : $data->nama_kontrakan,
                ]);
                return response()->json([
                    'status' => "success",
                    'message' => 'Update Successfully!',
                    'user'    => $user, 
                ], 200);
            }
    
            //user success login and create token
            return response()->json([
                'status' => "success",
                'message' => 'Update Successfully!',
                'user'    => $user, 
            ], 200);
        }else if(Auth::user()->role == "pengontrak"){
            User::where('id', $id)->update([
                'name'=>($request->name )? $request->name : $data->name,
                'email'=>($request->email )? $request->email : $data->email,
                'umur'=>($request->umur )? $request->umur : $data->umur,
                'updated'=>date('Y-m-d'),
    
            ]);
            $user = User::where('id', $id)->first();
    
            //user success login and create token
            return response()->json([
                'status' => "success",
                'message' => 'Update Successfully!',
                'user'    => $user, 
            ], 200);
        }

    }

    public function getUserPengontrak(){
        if(Auth::user()->role == "pengontrak"){
            return response()->json([
                'status' => "failed",
                'message' => 'you havent to access this page',
            ]);
        }
        $user = User::where('role', 'pengontrak')->where('nama_kontrakan',Auth::user()->nama_kontrakan)->get();
        if($user){
            return response()->json([
                'status' => "success",
                'message' => 'Get User Successfully!',  
                'user' => $user
            ]);
        }else{
            return response()->json([
                'status' => "error",
                'message' => 'Get User Failed!',  
            ],400);
        }

    }

    public function detailPengontrak($id){
        $user = User::where('role', 'pengontrak')->where('id',$id)->first();
        if($user){
            return response()->json([
                'status' => "success",
                'message' => 'Get User Successfully!',  
                'user' => $user
            ]);
        }else{
            return response()->json([
                'status' => "error",
                'message' => 'Get User Failed!',  
            ],400);
        }

    }

    public function deletePengontrak($id){
        $user = User::where('role', 'pengontrak')->where('id',$id)->first();
        if($user){
            $user->delete();
            return response()->json([
                'status' => "success",
                'message' => 'Delete User Successfully!',  
            ]);
        }else{
            return response()->json([
                'status' => "error",
                'message' => 'Delete User Failed!',  
            ],400);
        }

    }

    public function getNamaKontrakan(){
        $user = User::where('role', 'pemilik')->get('nama_kontrakan');
        if($user){
            return response()->json([
                'status' => "success",
                'message' => 'Get nama kontrakan Successfully!',  
                'user' => $user
            ],200);
        }else{
            return response()->json([
                'status' => "error",
                'message' => 'Get nama kontrakan Failed!',  
            ],400);
        }

    }

    public function editProfilePengontrak(Request $request){
        $profile = User::where('id',Auth::user()->id)->first();
        $validator = Validator::make($request->all(), [
            'email' => 'email|unique:users,email,'.$profile->email,
            'name'=>'string',
            'umur'=>'integer',

        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        User::where('id', Auth::user()->id)->update([
            'name'=>($request->name )? $request->name : $profile->name,
            'email'=>($request->email )? $request->email : $profile->email,
            'umur'=>($request->umur )? $request->umur : $profile->umur,
            'updated'=>date('Y-m-d'),

        ]);
        $user = User::where('id', Auth::user()->id)->first();

        //user success login and create token
        return response()->json([
            'status' => "success",
            'message' => 'Update Successfully!',
            'user'    => $user, 
        ], 200);

    }
}
