<?php

namespace App\Core\Trait;
use Filament\Forms\Set;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
trait GenerateFieldsTrait
{
    public static function GenerateFields(array $fields, $params = null){
        return array_map(function($field) use ($params) {
            $input = Grid::make()->schema([]);

            switch($field["type"] ){
            
                case "input":{
                    $input = TextInput::make($field['name'])
                    ->required($field['required'] ?? false)
                    ->lazy($field['lazy'] ?? false)
                    ->maxLength($field['maxLength'] ?? null);
        
                    if (isset($field['live'])) {
                        $input->live($field['live']);
                    }
                    
                    if (isset($field['callback'])) {
                        dd("opl");
                        if(method_exists($this, $field['callback'])) {
                            dd("okl");
                            $input->afterStateUpdated($this->{$field['callback']}(...));
                        }
                       // $input->afterStateUpdated($field['callback']);
                    }
                    break;
                }
                case "select" :{
                    $input = Select::make($field['name'])
                    ->options(
                    array_map(function($val){
                                return $val;
                            }, $field["options"]) 
                        
                        )
                    ->required($field['required'] ?? false)
                    ->live($field['live'] ?? false)
                    ->lazy($field['lazy'] ?? false);
                    if(isset($field['default'])){
                        $input->afterStateHydrated(function(Set $set) use($field){
                            $set($field['name'], $field["default"]);
                        });
                    }
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
                                            if (method_exists(self::class, $callbackKey)) {
                                                // Add $state as the first parameter
                                                $paramsWithState = array_merge([$state], $params);
                                    
                                                // Call the callback method with the state and other parameters
                                                call_user_func_array([self::class, $callbackKey], $paramsWithState);
                                            } else {
                                                throw new \Exception("Method $callbackKey does not exist in " . self::class);
                                            }                                        }
                                    );
                                    break;
                                }
                            }
                        }
                        // if (method_exists(self::class, $callbackMethod)) {
                        //     // Utilisation correcte en passant une fonction anonyme en tant que callback
                        //     $input->afterStateUpdated(function($state) use ($callbackMethod, $params) {
                        //         // dd($state);
                        //         // Ajoutez $state aux params si nécessaire
                        //         $paramsWithState = array_merge([$state], $params); // Ajoute $state comme premier paramètre

                        //         call_user_func_array([self::class, $callbackMethod],  $paramsWithState);
                        //     });
                        // }
                    }
                }
            }
            
            return $input;
        }, $fields);
    }
}
