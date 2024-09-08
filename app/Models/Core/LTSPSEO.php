<?php

namespace App\Models\Core;

use Spatie\Tags\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LtspSeo extends Model
{
    use HasFactory, HasTags;
    protected $fillable = [
        "meta_title",
        "meta_description",
        "is_redirection",
        "category_id"
    ];
}
