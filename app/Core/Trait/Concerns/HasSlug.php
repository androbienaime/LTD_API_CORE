<?php

namespace App\Core\Trait\Concerns;

use App\Models\Core\Account;
use App\Models\Core\Product;
use Illuminate\Support\Str;

trait HasSlug
{
    public static function createUniqueSlug($slug){
        $slug = Str::slug($slug);
        $accountConnected = Account::find(auth("account")->id());
        $shop = Account::findShopByAccount($accountConnected);

        $count = (new static)->newQuery()
            ->withoutGlobalScopes()
            ->where("slug", 'LIKE', "{$slug}%")
            ->orWhere("slug", 'LIKE', "{$shop->slug}-{$slug}%")
            ->count();

        if ($count > 0) {
            $accountConnected = Account::find(auth("account")->id());
            $shop = Account::findShopByAccount($accountConnected);
            return "{$shop->slug}-{$slug}-{$count}";
        }
        return $count > 0 ? (string) "{$shop->slug}-{$slug}-{$count}" : $slug;    
    }

    public static function findBySlug(string $slug){
        if(!Product::where("slug", $slug)->first()){
            return null;
        }

        return Product::where("slug", $slug)->first();
    }
}
