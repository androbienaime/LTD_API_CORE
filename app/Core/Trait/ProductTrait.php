<?php

namespace App\Core\Trait;

use Carbon\Carbon;
use App\Models\Core\Product;
use App\Models\Core\Delivery;

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

    public static function hasDeclinations(?Product $product){
        if($product == null){ 
            return; 
        }

        $hasDeclination = false;
        if($product->product_with_declination == true && $product->declinations->count() > 0){
            $hasDeclination = true;
           //
        }

        return $hasDeclination;
    }

    public static function productDiscount(?Product $product){
        $discount = 0;
        if($product != null){
            if($product->has_discount &&                                         
                Carbon::parse($product->productDiscount->end_date)->isFuture()){
                    $discount = $product->productDiscount->discount;
            }
        }

        return $discount;
    }

    public static function productDeliveryCosts(?Delivery $delivery){
        $deliveryPrice = 0;
       
        if($delivery != null){
            if($delivery->costs){
                $deliveryPrice = $delivery->costs;
            }
        }
        return $deliveryPrice;
    }

    public static function hasDelivery(?Product $product){
        $hasDelivery = false;
        if($product != null){
            if(count($product->deliveryProducts) > 0){
                $hasDelivery = true;
            }
        }

        return $hasDelivery;
    }

}
