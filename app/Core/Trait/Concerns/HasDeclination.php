<?php

namespace App\Core\Trait\Concerns;

use App\Models\Core\Product;

trait HasDeclination{
    public static function hasDeclinations(?\Illuminate\Database\Eloquent\Model $model){
        if($model == null){
            return;
        }

        $hasDeclination = false;
        if($model->has_declination == true && $model->declinations->count() > 0){
            $hasDeclination = true;
            //
        }

        return $hasDeclination;
    }

    public static function getDeclinations(\Illuminate\Database\Eloquent\Model $model){
        if(!self::hasDeclinations($model)){
            return null;
        }

        return $model->declinations->first()->values;
    }

    public static function getAttributeToArray(\Illuminate\Database\Eloquent\Model $model){
        if(!self::hasDeclinations($model)){
            return null;
        }
        $options = [];

        foreach($model->declinations as $pd){
            foreach($pd->values as $value){
                $attribute = $value->attribute;
                $options[$attribute->id][] = $value;
            }

        }

        return $options;

    }
}
