<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apikey extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'key'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }


}
