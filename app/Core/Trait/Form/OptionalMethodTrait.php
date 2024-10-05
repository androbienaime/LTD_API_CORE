<?php

namespace App\Core\Trait\Form;
use Str;

trait OptionalMethodTrait
{
    public static function afterStateUpdated($state, $callbackMethod, $params = [])
    {
        if (method_exists(static::class, print_r($callbackMethod))) {
            // Add $state as the first parameter
            $paramsWithState = array_merge([$state], $params);

            // Call the callback method with the state and other parameters
            call_user_func_array([self::class, $callbackMethod], $paramsWithState);
        } else {
            throw new \Exception("Method $callbackMethod does not exist in " . static::class);
        }
    }

}
