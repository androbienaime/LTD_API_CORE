<?php

namespace App\Models\Core;

use App\Core\Trait\Concerns\HasSlug;
use App\Models\Core\CategoryProduct;
use Illuminate\Database\Eloquent\Model;
use App\Core\Trait\Concerns\HasGeneralStatus;
use App\Core\Trait\Models\AccountGlobalScopeTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Core\States\GeneralStatus\GeneralStatusState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, AccountGlobalScopeTrait, HasSlug, HasGeneralStatus;

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

}
