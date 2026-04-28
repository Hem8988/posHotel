<?php

namespace Modules\RestApi\Entities;

use Illuminate\Database\Eloquent\Model;

class RestApiGlobalSetting extends Model
{
    protected $table = 'restapi_global_settings';

    const MODULE_NAME = 'RestApi';

    protected $guarded = ['id'];
}
