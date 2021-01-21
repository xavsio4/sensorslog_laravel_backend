<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MeasureResource extends JsonResource
{
    /**
    * Transform the resource into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
        'id' => $this->id,
        // 'user_id' => $this->user_id,
        'measure_value' => $this->measure_value,
        'measure_type' => $this->measure_type,
        'measure_unit' => $this->measure_unit,
        'created_at' => (string) $this->created_at,
        //'updated_at' => (string) $this->updated_at,
        'origin' => $this->origin,
        ];
    }
    
    public function with($request)
    {
        return [
        'meta' => [
        'key' => 'value',
        ],
        ];
    }
}