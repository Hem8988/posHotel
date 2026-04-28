<?php

$addOnOf = 'tabletrack';

return [
    'name' => 'RestApi',
    'verification_required' => true,
    'envato_item_id' => 61504968,
    'parent_envato_id' => 55116396, // TableTrack Envato ID
    'parent_min_version' => '1.2.1',
    'script_name' => $addOnOf . '-restapi-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\RestApi\Entities\RestApiGlobalSetting::class,
];
