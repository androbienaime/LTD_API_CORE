<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LtspSeo extends Model
{
    use HasFactory, HasTags;
    protected $fillable = [
        "meta_title",
        "meta_description",
        "is_redirection",
        
    ]
}
