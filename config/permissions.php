<?php

return [
    [
        'name' => 'API settings',
        'flag' => 'api.settings',
        'parent_flag' => 'settings.options',
    ],
    [
        'name' => 'Sanctum Token',
        'flag' => 'api.sanctum-token.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'api.sanctum-token.create',
        'parent_flag' => 'api.sanctum-token.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'api.sanctum-token.destroy',
        'parent_flag' => 'api.sanctum-token.index',
    ],
];
