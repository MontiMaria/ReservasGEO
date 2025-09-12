<?php

namespace App\Casts;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TimeCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  string|Carbon|null  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        if(empty($value)) {
            return null;
        }

        try {
            $time = Carbon::parse($value);
            return $time->format('H:i:s');
        }
        catch(Exception $key) {
            return $value;
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param string|Carbon|null    $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if(empty($value)) {
            return [$key => '00:00:00'];
        }

        return [
            $key=> $value ? Carbon::parse($value)->format("H:i:s") : null
        ];
    }
}
