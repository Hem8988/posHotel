<?php

namespace Modules\RestApi\Entities;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = 'ai_device_tokens';

    protected $guarded = ['id'];
}

