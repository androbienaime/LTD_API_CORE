<?php

namespace App\Core\Trait\Form;

use Filament\Forms\Components\Select;

trait SelectFieldTrait
{
    public static function selectField(array $field, $params = null){
       $input = Select::make($field['name'])
        ->options(
            array_map(function($val){
                    return $val;
                }, $field["options"]) 
            
            )
        ->required($field['required'] ?? false)
        ->live($field['live'] ?? false)
        ->lazy($field['lazy'] ?? false);
    
        if(isset($field["dehydrated"])){
            $input->dehydrated($field["dehydrated"]);
        }
        if (isset($field['callback'])) {
            $callback = $field['callback'];
            foreach($callback as $callbackKey => $callbackValue){
                switch($callbackKey){
                    case 'afterStateUpdated':{
                        $input->afterStateUpdated(
                            function ($state) use ($callbackKey, $params) {
                                return OptionalMethodTrait::afterStateUpdated( $state, $callbackKey, $params);
                            }
                        );
                        break;
                    }
                }
            }
        }   

        return $input;
    }
}
