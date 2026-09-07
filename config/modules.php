<?php

return [
    'base_path' => app_path('Modules'),
    'namespace' => 'App\\Modules',
    //array for short name
    'aliases' => [
        'ai' => 'AI',
        'api' => 'Api',
        'audit' => 'Audit',
        'authori' => 'Authorization',
        'identity' => 'Identity',
        'obs' => 'Observability',
        'org' => 'Organization',
        'proj' => 'Project',
        'task' => 'Task',
    ],
    'api_prefix' => 'api',
];
