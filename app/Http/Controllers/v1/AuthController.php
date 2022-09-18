<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\RefreshTokenRepository;

class AuthController extends Controller
{
    public $successStatus = 200;
    public function login(Request $request) { 
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required',

        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 400);            
        }
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) { 
           
            return $this->getTokenAndRefreshToken( request('email'), request('password'));
        } 
        else { 
            return response()->json(['error'=>'Unauthorised'], 401); 
        } 
    }

    public function register(Request $request) { 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'password' => 'required', 
            'password_confirm' => 'required|same:password', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 400);            
        }
        $password = $request->password;
        $input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        $user = User::create($input); 
               
       return  $this->getTokenAndRefreshToken( $user->email, $password);
    }
    public function getTokenAndRefreshToken( $email, $password) { 
       
       
        $data = [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
            'username' => $email,
            'password' => $password,
            'scope' => '',
    ];

        $request = Request::create('/oauth/token', 'POST', $data);
        return app()->handle($request);
    }
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'refresh_token'=>'required'
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->error(),400]);
        }
        $data = [
            'grant_type'=>'refresh_token',
            'client_id'=>env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
            'client_secret'=>env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
            'refresh_token'=>$request->refresh_token,
        ];
        $request = Request::create('/oauth/token','POST',$data);
        return app()->handle($request);

    }
    public function logout(){
        $token = auth()->user()->token();

        $token->revoke();
        $token->delete();
        $refreshTokenRespository = app(RefreshTokenRepository::class);
        $refreshTokenRespository->revokeRefreshTokenByAccessTokenId($token->id);
        return response()->json(['message' => 'Logged out successfully']);


    }
    public function getUser(){
        return response()->json(['data'=>auth()->user()]);
    }
}
