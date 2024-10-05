<?php

namespace App\Core\Class;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use App\Core\Trait\Form\SelectFieldTrait;

class GenerateFields
{
    use SelectFieldTrait;

    public static function Generate(array $fields, $params = null){
        
        return array_map(function($field) use ($params) {
            $input = Grid::make()->schema([]);

            switch($field["type"] ){
            
                case "input":{
                    $input = TextInput::make($field['name'])
                    ->required($field['required'] ?? false)
                    ->lazy($field['lazy'] ?? false)
                    ->maxLength($field['maxLength'] ?? null);
        
                    if (isset($field['live'])) {
                        $input->live(onBlur: $field['live']);
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
                    $input = SelectFieldTrait::selectField($field, $params);
                }
            }
            
            return $input;
        }, $fields);
    }
}
