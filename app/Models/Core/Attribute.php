<?php

namespace App\Models\Core;

use App\Models\Core\Value;
use App\Models\Core\AttributeValue;
use Illuminate\Database\Eloquent\Model;
use App\Core\Trait\Models\AccountGlobalScopeTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Core\States\GeneralStatus\GeneralStatusState;
use App\Core\Trait\Concerns\HasGeneralStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    use HasFactory, AccountGlobalScopeTrait, HasGeneralStatus;

    protected static string $tableName = 'attributes';

    protected $fillable = [
        "name",
        "type",
        "shop_id",
        "account_id"
    ];

    protected $casts = [
        "status" => GeneralStatusState::class,
        "status_data" => 'array'
    ];

    public function attributeDeclinations() : HasMany{
        return $this->hasMany(AttributeDeclination::class);
    }

    public function values() : HasMany{
        return $this->hasMany(Value::class);
    }
}
