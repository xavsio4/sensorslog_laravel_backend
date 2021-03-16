<?php

namespace App\Http\Controllers\api\v1;

use Validator;
use Illuminate\Http\Request;
use App\Models\Measure;
use App\Http\Resources\MeasureResource;
use App\Models\Apikey;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class MeasureController extends Controller
{
    
    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create','get','counting']]);
        //$this->user = JWTAuth::parseToken()->authenticate();
    }
    
    
    /**
    * @OA\Get(
    *      path="api/v1/measures",
    *      operationId="index",
    *      tags={"Measures"},
    *      summary="Get list of measures from the most recent to the oldest",
    *      description="Returns list of measures",
    * @OA\Parameter(
    *          api_key="api_key",
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Successful operation",
    *          @OA\JsonContent(ref="#/models/Measure")
    *       ),
    *      @OA\Response(
    *          response=401,
    *          description="Unauthenticated",
    *      ),
    *      @OA\Response(
    *          response=403,
    *          description="Forbidden"
    *      )
    *     )
    */
    public function index(request $request)
    {
        $user = $this->guard()->user();
        
        $measures = Measure::where('user_id',$user->id)->orderBy('created_at','desc')->get();
        // $measures = Measure::all();
        return response()->json([
        'success' => true,
        'data' => $measures
        ]);
        
    }
    
    
    
    /**
    * @OA\Post(
    *      path="api/v1/measures/create",
    *      operationId="create",
    *      tags={"Measures"},
    *      summary="Create a measure",
    *      description="Create the measures a return a json with the created measure",
    * @OA\Parameter(
    *          api_key="api_key",
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Successful operation",
    *          @OA\JsonContent(ref="#/models/Measure")
    *       ),
    *      @OA\Response(
    *          response=401,
    *          description="Unauthenticated",
    *      ),
    *      @OA\Response(
    *          response=403,
    *          description="Forbidden"
    *      )
    *     )
    */
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
        //but hey, in this app context it is better do like below
        
        
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
    *
    */
    public function get(request $request)
    {
        $validator = Validator::make($request->all(), [
        'key' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        //get the user_id from the key
        $apikey = Apikey::where('key',$request->key)->first();
        
        //$input = $request->except(['key','page']);
        $input = $request->only(['origin','measure_type','measure_unit']);
        
        $result= MeasureResource::collection(Measure::where('user_id',$apikey->user_id)->where($input)->orderBy('created_at','desc')->paginate());
        
        //because we are using collections from resources
        return $result;
    }
    
    /**
    * @OA\Get(
    *      path="api/v1/measures/latest",
    *      operationId="latest",
    *      tags={"Measures"},
    *      summary="Get the latest measure",
    *      description="Returns the latest measure",
    * @OA\Parameter(
    *           api_key="api_key",
    *           measure_type="measure_type",
    *           origine="origine"
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Successful operation",
    *          @OA\JsonContent(ref="#/models/Measure")
    *       ),
    *      @OA\Response(
    *          response=401,
    *          description="Unauthenticated",
    *      ),
    *      @OA\Response(
    *          response=403,
    *          description="Forbidden"
    *      )
    *     )
    */
    public function getLatest(request $request)
    {
        $user = $this->guard()->user();
        
        $measures = DB::select('select measure_type,origin,measure_unit, max(measure_value) as measure_value ,max(created_at) as created_at from measures where user_id = :id group by measure_type,origin,measure_unit', ['id' => $user->id]);
        return response()->json([
        'success' => true,
        'data' => $measures
        ]);
    }
    
    public function getDynamicTypes(request $request)
    {
        $user = $this->guard()->user();
        
        $measures = Measure::select('measure_type')->distinct('measure_type')->where('user_id',$user->id)->get();
        // $measures = Measure::all();
        return response()->json([
        'success' => true,
        'data' => $measures
        ]);
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
    
    public function destroy(request $request)
    {
        $measure = Measure::find($request->id);
        
        if (!$measure) {
            return response()->json([
            'success' => false,
            'message' => 'Measure not found'
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
    }
    
    /**
    * Destroy All
    */
    public function destroyall()
    {
        $user = $this->guard()->user();
        $measure = Measure::where('user_id',$user->id)->delete();
        
        return response()->json(['status'=>'success']);
        
    }
    
    /**
    * Destroy filtered
    */
    public function destroyfiltered(request $request)
    {
        $validator = Validator::make($request->all(), [
        'filter' => 'required'
        ]);
        
        $filter = $request->filter;
        
        $result = Measure::find()->delete();
        
        return response()->json(['status'=>'success']);
        
    }
    
    /**
    * Destroy selection
    */
    public function destroyselected(request $request)
    {
        $validator = Validator::make($request->all(), [
        'selection' => 'required',
        'inverse' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        
        $inverse = $request->inverse;
        $selection = $request->selection; //should be an array
        
        return $selection;
        
        if ($inverse == 0)
            $result = Measure::whereIn('id',$selection)->delete();
        else
            $result = Measure::whereNotIn('id',$selection)->delete();
        
        return $result;
        
        return response()->json(['status'=>'success']);
        
    }
    
    
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