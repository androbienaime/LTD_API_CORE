<?php

namespace App\Models\Core;

use App\Core\Trait\Concerns\HasDeclination;
use App\Core\Trait\Concerns\HasStatus;
use App\Core\Trait\Models\AccountGlobalScopeTrait;
use App\Core\Trait\Models\AccountShopTrait;
use Spatie\Tags\HasTags;
use App\Models\Core\Shop;
use App\Models\Core\Brand;
use Illuminate\Support\Str;
use App\Models\Core\Carrier;
use App\Models\Core\LtspSeo;
use App\Models\Core\Category;
use App\Models\Core\Currency;
use App\Models\Core\Customer;
use App\Models\Core\Delivery;
use App\Models\Core\Declination;
use App\Models\Core\BrandProduct;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Core\CommentProduct;
use App\Models\Core\CategoryProduct;
use App\Models\Core\DeliveryProduct;
use App\Models\Core\ProductDiscount;
use App\Models\Core\DeclinationProduct;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Cache\RateLimiting\Unlimited;
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
    use HasFactory,
        InteractsWithMedia,
        HasTags,
        AccountGlobalScopeTrait,
        AccountShopTrait,
        HasStatus,
        HasDeclination;

    protected $guarded;

    protected static string $tableName = 'products';

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

    public function brands() : BelongsToMany{
        return $this->belongsToMany(Brand::class);
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

    public function productDiscount() : BelongsTo{
        return $this->belongsTo(ProductDiscount::class);
    }
    public static function createUniqueSlug($name){
        $slug = Str::slug($name);
        $accountConnected = Account::find(auth("account")->id());
        $shop = self::findShopByAccount($accountConnected);

        $count = Product::where("slug", 'LIKE', "{$slug}%")->count();

        return $count > 0 ? (string) "{$shop->slug}-{$slug}-{$count}" :$slug;
    }

    public function productCover()
    {
        return $this->getFirstMedia() ? $this->getFirstMedia()->getUrl("thumb") : null;
    }


}
