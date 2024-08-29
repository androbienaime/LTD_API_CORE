<?php 

namespace App\Core\Trait;
use Illuminate\Database\Eloquent\Model;

trait FillTableToManyTrait{
    public static function processFillTable(array $data, $relatedModel, $relationKey){ 
        $relatedM = $relatedModel::createOrFirst($data);
        unset($data);
        $data[$relationKey] = $relatedM->id;
  

        
        return $data;
    }
}