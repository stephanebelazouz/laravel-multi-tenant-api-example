<?php

return [
    'tenant_model' => \App\Models\Tenant::class,
    'id_generator' => Stancl\Tenancy\UuidGenerator::class,

    'central_domains' => [
        'localhost',
    ],

    'database' => [
        'central_connection' => 'pgsql',
        'template_tenant_connection' => 'pgsql',
        'prefix' => 'tenant_',
        'suffix' => '',
        'separate_database' => true,
        'managers' => [
            'pgsql' => \Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class
        ],
    ],

    'features' => [
        Stancl\Tenancy\Features\TenantConfig::class,
    ],

    'migration_parameters' => [
        '--force' => true,
        '--path' => [database_path('migrations/tenant')],
        '--realpath' => true,
    ],

    'seeder_parameters' => [
        '--class' => 'DatabaseSeeder',
    ],
];
