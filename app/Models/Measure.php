<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Measure extends Model
{
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
    'measure_type', 'measure_value','measure_unit','origin'
    ];
    
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    
    
}