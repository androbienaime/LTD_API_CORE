<?php

namespace App\Models\Core;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\BelongsToManyRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use App\Models\Core\Product;
use App\Models\Core\Customer;
use App\Models\Core\MerchantShop;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model implements HasMedia, HasName
{
    use HasFactory, InteractsWithMedia;

    // Définir les valeurs de l'énumération
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        "name",
        "reference",
        "theme_name",
        "theme_color",
        "status",
        "types",
        "shop_description",
        "address_id",
        "ltsp_seo_id",
        "slug"
    ];

    protected $casts = [
        "shop_cover" => 'array',
        "shop_profile" => 'array',
    ];

    public function merchantShops(): HasMany{
        return $this->hasMany(MerchantShop::class);
    }

    public function account() : BelongsToMany{
        return $this->belongsToMany(Account::class);
    }

    public function subscribers(){
        return $this->belongsToMany(Customer::class, 'subscriptions');
    }

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class, 'category_shop');
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function ltspSeo(){
        return $this->belongsTo(LtspSeo::class);
    }

     /**
     * Obtenir les statuts possibles.
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
            self::STATUS_SUSPENDED,
        ];
    }

    /**
     * Vérifier si le shop est actif.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Vérifier si le shop est en attente.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifier si le shop est suspendu.
     *
     * @return bool
     */
    public function isSuspended()
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public static function createUniqueSlug($name){
        $slug = Str::slug($name);
        $count = Shop::where("slug", 'LIKE', "{$slug}%")->count();

        return $count > 0 ? (string) "{$slug}-{$count}" :$slug;
    }

    public function getDescriptionAttribute(){
        return strip_tags($this->shop_description);
    }

    public function getFulladdressAttribute()
    {
        $addressParts = array_filter([
            $this->address?->country?->name,
            $this->address?->state?->name,
            $this->address?->city?->name,
        ]);

        return implode(', ', $addressParts);
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function accounts() : BelongsToMany{
        return $this->belongsToMany(Account::class);
    }
}
