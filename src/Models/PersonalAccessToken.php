<?php

namespace Tiryaq\Api\Models;

use Tiryaq\Base\Contracts\BaseModel;
use Tiryaq\Base\Models\Concerns\HasBaseEloquentBuilder;
use Tiryaq\Base\Models\Concerns\HasMetadata;
use Tiryaq\Base\Models\Concerns\HasUuidsOrIntegerIds;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken implements BaseModel
{
    use HasMetadata;
    use HasUuidsOrIntegerIds;
    use HasBaseEloquentBuilder;
}
