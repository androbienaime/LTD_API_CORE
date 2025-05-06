<?php

namespace App\Models\Core;

use Spatie\MediaLibrary\HasMedia;
use App\Core\Trait\Concerns\HasSlug;
use App\Models\Core\CategoryProduct;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Core\Trait\Concerns\HasGeneralStatus;
use App\Core\Trait\Models\AccountGlobalScopeTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Core\States\GeneralStatus\GeneralStatusState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model implements HasMedia
{
    use HasFactory, AccountGlobalScopeTrait, HasSlug, HasGeneralStatus, InteractsWithMedia;


    protected static string $tableName = 'categories';

    protected $fillable = [
        "name",
        "category_parent",
        "slug",
        "parent_id",
        "shop_id",
        "accoun_id"
    ];

    protected $casts = [
        "status" => GeneralStatusState::class,
        "status_data" => 'array'
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function Product() : BelongsToMany{
        return $this->belongsToMany(CategoryProduct::class);
    }

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
    

}
