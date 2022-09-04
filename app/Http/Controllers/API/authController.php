<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class authController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required',
            'rooms'=>'required',
            'nama_kontrakan'=>'required',

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
                'message' => 'Login Failed!',
            ], 400);
        }

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'rooms'=>$request->rooms,
            'nama_kontrakan'=>$request->nama_kontrakan,
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

        ]);

        //if validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        User::where('id', $id)->update([
            'name'=>($request->name )? $request->name : $data->name,
            'email'=>($request->email )? $request->email : $data->email,
            'rooms'=>($request->rooms )? $request->rooms : $data->rooms,
            'nama_kontrakan'=>($request->nama_kontrakan )? $request->nama_kontrakan : $data->nama_kontrakan,
            'updated'=>date('Y-m-d'),

        ]);
        $user = User::where('email', $request->email)->first();

        //user success login and create token
        return response()->json([
            'status' => "success",
            'message' => 'Update Successfully!',
            'user'    => $user, 
        ], 200);
    }
}
