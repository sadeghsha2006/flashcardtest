<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\RefreshTokenRepository;
use Illuminate\Validation\Rules\Password;


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

            return $this->getTokenAndRefreshToken(auth()->user(), request('email'), request('password'));
        }
        else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' =>[
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],

        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        $password = $request->password;
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $personalToken = $user->createToken('mytoken')->accessToken;

       $result =  $this->getTokenAndRefreshToken( $request->email,$request->password);
       $output = json_decode($result->original);

       return  response()->json([
          "token_type"=>$output->token_type,
           "expires_in"=>$output->expires_in,
           "access_token"=>$output->access_token,
           "refresh_token"=>$output->refresh_token,
            "data"=>$user,
       ]);

    }
    public function getTokenAndRefreshToken(  $username,$password) {


        $data = [

            'grant_type' => 'password',
            'client_id' => env('PASSPORT_PASSWORD_GRANT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_GRANT_CLIENT_SECRET'),
            'username' => $username,
            'password' =>$password,
            'scope' => '',

    ];

        $req = Request::create('/oauth/token', 'POST', $data);

       return  app()->handle($req);



    }
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'refresh_token'=>'required'
        ]);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors(),400]);
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
    public function logout(Request $request){
        auth()->user()->token()->revoke();

        return response()->json(['message' => 'Logged out successfully']);


    }
    public function getUser(){
        
        return response()->json(['data'=>auth()->user()]);
    }
}
