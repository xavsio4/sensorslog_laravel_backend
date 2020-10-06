<?php

namespace app\Http\Controllers\api\v1;


use Illuminate\Http\Request;
use App\Models\Apikey;
use App\Http\Controllers\Controller;
use Keygen;


class ApikeyController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
         $this->middleware('auth:api');
         //$this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index(request $request)
    {

            //$measures = Measure::where('user_id',$user->id)->get();
            $apikeys = Apikey::all();
            return response()->json([
                'success' => true,
                'data' => $apikeys
            ]);

    }

    public function create()
    {

        $apikey = new Apikey;

         $key = Keygen::numeric(26)->generate();

        $apikey->key = $key;
        $apikey->user_id = ;

        $result = $apikey->save();

        return response()->json($result, 201);
    }

    public function store(Request $request)
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
    }

    public function update(Request $request, $id)
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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

    }

    public function destroy($id)
    {
        $post = auth()->user()->posts()->find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 400);
        }

        if ($post->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post can not be deleted'
            ], 500);
        }
    }

    public function Counting()
    {
        $count = Measure::count();
        return response()->json([
            'data' => $count
        ]);
    }

}
