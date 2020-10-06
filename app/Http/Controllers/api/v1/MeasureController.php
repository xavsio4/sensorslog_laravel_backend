<?php

namespace App\Http\Controllers\api\v1;

use Validator;
use Illuminate\Http\Request;
use App\Models\Measure;
use App\Models\Apikey;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class MeasureController extends Controller
{
    
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create','counting']]);
        //$this->user = JWTAuth::parseToken()->authenticate();
    }
    
    /**
    * List the measures for the frontend
    */
    public function index(request $request)
    {
        $user = $this->guard()->user();
        
        $measures = Measure::where('user_id',$user->id)->get();
        // $measures = Measure::all();
        return response()->json([
        'success' => true,
        'data' => $measures
        ]);
        
    }
    
    public function create(request $request)
    {
        $validator = Validator::make($request->all(), [
        //'measure_type' => 'required',
        'measure_value' => 'required',
        //'measure_unit' => 'required',
        'key' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        //get the user_id from the key
        $apikey = Apikey::where('key',$request->key)->first();
        
        //I leave this one as for information purpose
        //so you see this eloquent command exists too
        //$measure = Measure::create($request->all());
        //but hey, better do like below in our context
        
        
        if ($apikey) { //did we found someone ?
            $measure = New Measure;
            $measure->user_id = $apikey->user_id;
            $measure->measure_type = $request->measure_type;
            $measure->measure_value = $request->measure_value;
            $measure->measure_unit = $request->measure_unit;
            $measure->origin = $request->origin;
            $measure->save();
            return response()->json($measure, 201);
        } else {
            return response()->json(['status'=> 401,"message"=>"Something went terribly wrong and it looks you'd better not do that anymore."], 401);
        }
        
        //just in case
        return response()->json(['status'=> 401,"message"=>"Something went terribly wrong and it looks you'd better not do that anymore."], 401);
        
    }
    
    /**
    * That would have been the one without the key in the url
    */
    
    /*  public function store(Request $request)
    {
    $this->validate($request, [
    'measure_type' => 'required',
    'measure_value' => 'required',
    'measure_unit' => 'required'
    ]);
    
    $post = new Measure();
    $post->measure_type = $request->measure_type;
    $post->measure_value = $request->measure_value;
    
    if (auth()->user()->measures()->save($post))
    return response()->json([
    'success' => true,
    'data' => $post->toArray()
    ]);
    else
    return response()->json([
    'success' => false,
    'message' => 'Measure not added'
    ], 500);
    } */
    
    
    /**
    * Not going to use that but..hey now you can
    * copy paste some code
    */
    /* public function update(Request $request, $id)
    {
    $post = auth()->user()->measures()->find($id);
    // $contact = Contact::find($id);
    
    if (!$post) {
    return response()->json([
    'success' => false,
    'message' => 'Post not found'
    ], 400);
    }
    
    $updated = $post->fill($request->all())->save();
    
    if ($updated)
    return response()->json([
    'success' => true
    ]);
    else
    return response()->json([
    'success' => false,
    'message' => 'Measure can not be updated'
    ], 500);
    } */
    
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    * example with jwt
    */
    /* public function show($id)
    {
    if (!($user = JWTAuth::parseToken()->authenticate())) {
    return response()->json(['user_not_found'], 404);
    }
    else {
    $measure = Measure::where('user_id',$user->id)->where('id',$id)->get();
    if ($measure)
    return response()->json([
    'success' => true,
    'data' => $measure
    ]);
    else
    return response()->json([
    'success' => false,
    'message' => 'Not found or unauthorized'
    ], 500);
    }
    
    } */
    
    /*  public function destroy(request $request)
    {
    $measure = Measure::find($request->id);
    
    if (!$measure) {
    return response()->json([
    'success' => false,
    'message' => 'Post not found'
    ], 400);
    }
    
    if ($measure->delete()) {
    return response()->json([
    'success' => true
    ]);
    } else {
    return response()->json([
    'success' => false,
    'message' => 'Measure can not be deleted'
    ], 500);
    }
    } */
    
    
    /**
    * Yeah I display this in the front of the frontend
    * Surely there is got to be a better way...
    */
    public function Counting()
    {
        $count = Measure::count();
        return response()->json([
        'data' => $count
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
    
}