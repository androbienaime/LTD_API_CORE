<?php

namespace App\Core\Trait;

trait ProductTrait
{
    public static function attributesExist($options, $attributes){

            // Parcourir chaque groupe d'options
        foreach ($options as $attributeName => $values) {
            // Parcourir les sous-valeurs du tableau (les sous-éléments)
            foreach ($values as $option) {

                // Vérifier si l'ID correspond
                if ($option->id == $attributes->id) {
                    return true; // Si trouvé, retourner immédiatement true
                }
            }
        }

        
        // Si aucune correspondance n'a été trouvée, retourner false
        return false;
    }
}
