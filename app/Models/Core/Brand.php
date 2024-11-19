<?php

namespace App\Models\Core;

use App\Models\Core\Product;
use App\Models\Core\BrandProduct;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Core\Trait\Concerns\HasGeneralStatus;
use App\Core\Trait\Models\AccountGlobalScopeTrait;
use App\Core\States\GeneralStatus\GeneralStatusState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Brand extends Model implements HasMedia
{
    use HasFactory, 
        InteractsWithMedia, 
        AccountGlobalScopeTrait,
        HasGeneralStatus;

    protected static string $tableName = 'brands';

    protected $fillable = [
        "name",
        "logo",
        "color",
        "shop_id",
        "account_id"
    ];

    protected $casts = [
        "status" => GeneralStatusState::class,
        "status_data" => 'array'
    ];

    public function registerMediaConversions(Media $media = null) : void{
        $this->addMediaConversion("thumb")
            ->width(1080)
            ->height(300)
            ->sharpen(10);

    }

    public function brandProducts() : BelongsTo{
        return $this->belongsTo(BrandProduct::class);
    }
    public function products() : BelongsToMany{
        return $this->belongsToMany(Product::class);
    }
}
