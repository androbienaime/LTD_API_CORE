<?php

namespace App\Models\Core;

use App\Core\States\GeneralStatus\ActiveState;
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
use App\Core\Trait\Concerns\HasSlug;
use App\Models\Core\CategoryProduct;
use App\Models\Core\DeliveryProduct;
use App\Models\Core\ProductDiscount;
use App\Core\Trait\Concerns\HasStatus;
use App\Models\Core\DeclinationProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Core\Trait\Concerns\HasDeclination;
use App\Core\Trait\Models\AccountShopTrait;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Cache\RateLimiting\Unlimited;
use App\Core\Trait\Concerns\HasGeneralStatus;
use Spatie\MediaLibrary\Conversions\Conversion;
use App\Core\Trait\Models\AccountGlobalScopeTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use App\Core\States\GeneralStatus\GeneralStatusState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\HasUuid;

/**
 * @method void prepareToAttachMedia(Media $media, FileAdder $fileAdder)
 */
class Product extends Model implements HasMedia
{
    use HasFactory,
        InteractsWithMedia,
        HasTags,
        AccountGlobalScopeTrait,
        HasGeneralStatus,
        HasDeclination,
        HasSlug;

    protected $fillable = [
        "name",
        "slug",
        "description",
        "article",
        "product_type",
        "sku",
        "price",
        "currency_id",
        "purchase_price",
        "stock_quantity",
        "min_stock_alert",
        "max_stock_alert",
        "min_cart",
        "max_cart",
        "is_available_market",
        "has_declination",
        "status",
        "is_downloadable",
        "is_trend",
        "is_in_stock",
        "has_multi_price",
        "has_unlimited_stock",
        "has_discount",
        "has_max_cart",
        "has_stock_alert",
        "status_data",
        "shop_id",
        "ltsp_seo_id",
        "product_discount_id",
        "merchant_id",
    ];

    protected $casts = [
        "status" => GeneralStatusState::class,
        "status_data" => 'array',
        "is_downloadable" => 'boolean',
        "is_available_market" => 'boolean',
        "has_declination" => 'boolean',
        "is_trend" => 'boolean',
        "is_in_stock" => 'boolean',
        "has_multi_price" => 'boolean',
        "has_unlimited_stock" => 'boolean',
        "has_discount" => 'boolean',
        "has_max_cart" => 'boolean',
        "has_stock_alert" => 'boolean',
    ];

    protected static string $tableName = 'products';

    public function registerMediaConversions(Media $media = null) : void{
        $this->addMediaConversion("thumb")
            ->width(1080)
            ->height(300)
            ->sharpen(10);

    }

    public function getImagesWithUrls()
    {
        return $this->getMedia('product_images')->map(function($media) {
            return [
                'original_url' => $media->getUrl(),
                'thumb_url' => $media->getUrl('thumb'),
                'file_name' => $media->file_name
            ];
        });
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

    public function productCover()
    {
        return $this->getFirstMedia() ? $this->getFirstMedia()->getUrl("thumb") : null;
    }

    // Scope pour filtrer les produits en stock et actifs
    public function scopeAvailable(Builder $query)
    {
        return $query->where(function($q) {
            $q->where('has_unlimited_stock', true)
              ->orWhere(function($subQ) {
                  $subQ->where('has_unlimited_stock', false)
                       ->where('stock_quantity', '>', 0);
              });
        })->where('status', ActiveState::class)
          ->where('is_in_stock', true);
    }

    // Vérifie si le produit est disponible
    public function isAvailable(): bool
    {

        return $this->isActivated()
        && $this->is_in_stock 
        && ($this->has_unlimited_stock || $this->stock_quantity > 0);    
    }

    // Récupérer les produits disponibles avec leurs médias
    public static function getAvailableProductsWithMedia()
    {
        return self::with(['media', 'categories', 'brands'])
                   ->available();
    }
}
