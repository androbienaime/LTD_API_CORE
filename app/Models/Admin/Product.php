<?php

namespace App\Models\Admin;

use App\Models\Customer;
use Spatie\Tags\HasTags;
use App\Models\Admin\Shop;
use Illuminate\Support\Str;
use App\Models\Admin\Carrier;
use App\Models\Admin\LtspSeo;
use App\Models\Admin\Currency;
use App\Models\Admin\Delivery;
use App\Models\Admin\Declination;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Admin\BrandProduct;
use App\Models\Admin\CommentProduct;
use App\Models\Admin\CategoryProduct;
use App\Models\Admin\DeliveryProduct;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\DeclinationProduct;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\Conversions\Conversion;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 */
class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTags;

    protected $guarded;

    public function registerMediaConversions(Media $media = null) : void{
        $this->addMediaConversion("thumb")
            ->width(1080)
            ->height(300)
            ->sharpen(10);
    }

    public function currency() : BelongsTo{
        return $this->belongsTo(Currency::class);
    }

    public function declinationProducts() : HasMany{
        return $this->hasMany(DeclinationProduct::class);
    }

    public function declinations(): HasMany{
        return $this->hasMany(Declination::class);
    }

    public function categoryProducts() : HasMany{
        return $this->hasMany(CategoryProduct::class);
    }

    public function categories() : BelongsToMany{
        return $this->belongsToMany(Category::class);
    }
    public function brandProducts() : HasMany{
        return $this->hasMany(BrandProduct::class);
    }

    public function deliveryProducts() : HasMany{
        return $this->hasMany(DeliveryProduct::class);
    }

    public function shop(){
        return $this->belongsTo(Shop::class);
    }
    
    public function productLikes(){
        return $this->belongsToMany(Customer::class, 'productLikes');
    }

    public function productShares(){
        return $this->belongsToMany(Customer::class, 'productShares');
    }

    public function commentProducts(){
        return $this->hasMany(CommentProduct::class);
    }

    public function ltspSeo() : BelongsTo{
        return $this->belongsTo(LtspSeo::class);
    }
    public static function createUniqueSlug($name){
        $slug = Str::slug($name);
        $count = Product::where("slug", 'LIKE', "{$slug}%")->count();

        return $count > 0 ? (string) "{$slug}-{$count}" :$slug;
    }
}
