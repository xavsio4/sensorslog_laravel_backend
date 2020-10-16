<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use App\Models\Measure;
use App\Models\Apikey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Keygen;

class AuthController extends Controller
{
    /**
    * Create a new AuthController instance.
    *
    * @return void
    */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','passwordForgot']]);
    }
    
    /**
    * Create requests api
    * in table apikeys
    */
    public function createapikey()
    {
        $apikey = new Apikey;
        
        $key = $this->generateNumericKey(36);
        $user = $this->guard()->user();
        
        $apikey->key = $key;
        $apikey->user_id = $user->id;
        
        $result = $apikey->save();
        
        return response()->json([
        'data' => $key,
        ], 201);
    }
    
    /**
    * Create requests api
    * in table apikeys
    */
    public function renewapikey()
    {
        $apikey = new Apikey;
        
        $key = $this->generateNumericKey(36);
        $user = $this->guard()->user();
        $apikey = Apikey::where('user_id',$user->id)->first();
        
        $apikey->key = $key;
        $result = $apikey->save();
        
        return response()->json([
        'data' => $key,
        ], 201);
    }
    
    /**
    * Remove Api key
    */
    public function deleteapikey()
    {
        $user = $this->guard()->user();
        $result = Apikey::where('user_id',$user->id)->delete();
        
        return response()->json($result, 201);
    }
    
    /**
    * List Api keys
    * to use by iot
    */
    public function apikeyList()
    {
        $user = $this->guard()->user();
        
        $apikeys = Apikey::where('user_id',$user->id)->first();
        return response()->json([
        'success' => true,
        'data' => $apikeys
        ]);
    }
    
    /**
    * Destroy user account
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function destroy(request $request)
    {
        $validator = Validator::make($request->all(), [
        'username' => 'required|string|between:2,100',
        ]);
        
        //Get authenticated user
        $user = $this->guard()->user();
        
        if ($user && $request->username == $user->name)
        {
            $uid = $user->id;
            
            //destroy measures
            // they could be destroyed by cascade with the user
            Measure::where('user_id',$uid)->delete();
            
            //logout for api token
            // $this->guard()->logout();
            //Auth::logout();
            if (Auth::check()) {
                Auth::user()->accessTokens()->delete();
            }
            
            //destroy user account
            // the api keys will be deleted by cascade
            User::where('id', $uid)->delete();
            
            return response()->json([
            'status_code' => 200,
            'message' => 'Account Destroyed',
            ], 201);
        }
        else
            return response()->json(['Not working !.'], \Illuminate\Http\Response::HTTP_UNAUTHORIZED);
        
    }
    
    /**
    * Register a User.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'name' => 'required|string|between:2,100',
        'email' => 'required|string|email|max:100|unique:users',
        'password' => 'required|string|confirmed|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
            'status_code' => 401,
            // 'message' => 'Bad request.'
            'message' => $validator->failed()
            ]
            , 401);
        }
        
        $user = User::create(array_merge(
        $validator->validated(),
        ['password' => bcrypt($request->password)]
        ));
        
        //auth()->login($user);
        
        /*   $resp = auth()->attempt(['email'=>$user->email,'password'=>$request->password]);
        auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('SPA');
        */
        return response()->json([
        'status_code' => 200,
        'message' => 'User successfully registered',
        'user' => $user,
        //  'token' => $token
        ], 201);
    }
    
    
    /**
    * Get a JWT token via given credentials.
    *
    * @param \Illuminate\Http\Request $request
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $cred = $request->only('email', 'password');
        
        if (auth()->attempt($cred)) {
            
            auth()->user()->tokens()->delete();
            
            $token = auth()->user()->createToken('SPA');
            
            return response()->json([
            'access_token' => $token->accessToken,
            ]);
        }
        
        
        return response()->json([
        'status_code' => 401,
        'message' => 'Login Failed'
        ]);
    }
    
    /**
    * passworg forgot
    *
    */
    public function passwordForgot(request $request)
    {
        $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        $status = Password::sendResetLink(
        $request->only('email')
        );
        
        if ($status === Password::RESET_LINK_SENT)
            return response()->json([
        'status_code' => 200,
        'message' => 'Password reset link sent !'
        ]);
        else
            return response()->json([
        'status_code' => 401,
        'message' => 'Somrthing is wrong ! Are you registered here ?'
        ]);
    }
    
    /**
    * Generate Personal Access Token
    */
    /* public function genpat(request $request) {
    // $user = User::find(2);
    
    $user = $this->guard()->user();
    
    // Creating a token without scopes...
    $token = $user->createToken('Token Name')->accessToken;
    
    // Creating a token with scopes...
    $token = $user->createToken('sensorslog_pat', ['measures'])->accessToken;
    
    return response()->json([
    'status' => 'success',
    'token' => $token
    ], 200);
    
    }*/
    
    
    /**
    * Get the authenticated User
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function profile()
    {
        try {
            return response()->json([
            'status_code' => 200,
            'message' => 'Success',
            'data' => $this->guard()->user(),
            ]);
            //return response()->json($this->guard()->user());
            //return response()->json(auth()->user());
        } catch (Exception $error) {
            return response()->json([
            'status_code' => 500,
            'message' => 'Not authorized',
            'error' => $error,
            ]);
        }
        
        
    }
    
    /**
    * Log the user out (Invalidate the token)
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function logout()
    {
        $this->guard()->logout();
        
        return response()->json([
        'status' => 'success',
        'msg' => 'Logged out Successfully.'
        ], 200);
    }
    
    /**
    * Refresh a token.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }
    
    /**
    * Get the token array structure.
    *
    * @param string $token
    *
    * @return \Illuminate\Http\JsonResponse
    */
    protected function respondWithToken($token)
    {
        return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }
    
    /**
    * Get the guard to be used during authentication.
    *
    * @return \Illuminate\Contracts\Auth\Guard
    */
    public function guard()
    {
        return Auth::guard('api');
    }
    
    // modified generateNumericKey() method
    // Ensures non-zero integer at beginning of key
    
    protected function generateNumericKey($len)
    {
        // prefixes the key with a random integer between 1 - 9 (inclusive)
        // return Keygen::numeric($len)->prefix(mt_rand(1, 9))->generate(true);
        return Keygen::token($len)->generate();
    }
}