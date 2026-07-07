<?php

return [

    'resources' => [
        'PermissionResource' => \Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource::class,
        'RoleResource' => \Althinect\FilamentSpatieRolesPermissions\Resources\RoleResource::class,
    ],

    'preload_roles' => true,

    'preload_permissions' => true,

    'navigation_section_group' => 'Configuración',

    'team_model' => \App\Models\Team::class,

    'scope_to_tenant' => false,

    'scope_roles_to_tenant' => false,
    'scope_premissions_to_tenant' => false,

    'super_admin_role_name' => 'Admin',

    'should_register_on_navigation' => [
        'permissions' => true,
        'roles' => true,
    ],

    'should_show_permissions_for_roles' => true,

    'should_use_simple_modal_resource' => [
        'permissions' => false,
        'roles' => false,
    ],

    'should_remove_empty_state_actions' => [
        'permissions' => false,
        'roles' => false,
    ],

    'should_redirect_to_index' => [
        'permissions' => [
            'after_create' => false,
            'after_edit' => false,
        ],
        'roles' => [
            'after_create' => false,
            'after_edit' => false,
        ],
    ],

    'should_display_relation_managers' => [
        'permissions' => true,
        'users' => true,
        'roles' => true,
    ],

    'clusters' => [
        'permissions' => null,
        'roles' => null,
    ],

    'guard_names' => [
        'web' => 'web',
    ],

    'toggleable_guard_names' => [
        'roles' => [
            'isToggledHiddenByDefault' => true,
        ],
        'permissions' => [
            'isToggledHiddenByDefault' => true,
        ],
    ],

    'default_guard_name' => 'web',

    'should_show_guard' => false,

    'model_filter_key' => 'return \'%\'.$value;',

    'user_name_column' => 'name',

    'user_name_searchable_columns' => ['name'],

    'icons' => [
        'role_navigation' => 'heroicon-o-lock-closed',
        'permission_navigation' => 'heroicon-o-lock-closed',
    ],

    'sort' => [
        'role_navigation' => 5,
        'permission_navigation' => 6,
    ],

    'generator' => [
        'guard_names' => [
            'web',
        ],

        'permission_affixes' => [
            'viewAnyPermission' => 'view_any',
            'viewPermission' => 'view',
            'createPermission' => 'create',
            'updatePermission' => 'update',
            'deletePermission' => 'delete',
            'deleteAnyPermission' => 'delete_any',
            'replicatePermission' => 'replicate',
            'restorePermission' => 'restore',
            'restoreAnyPermission' => 'restore_any',
            'reorderPermission' => 'reorder',
            'forceDeletePermission' => 'force_delete',
            'forceDeleteAnyPermission' => 'force_delete_any',
        ],

        'permission_name' => 'return $permissionAffix . \'_\' . $modelName;',

        'discover_models_through_filament_resources' => false,

        'model_directories' => [
            app_path('Models'),
        ],

        'custom_models' => [],

        'excluded_models' => [],

        'excluded_policy_models' => [
            \App\Models\User::class,
        ],

        'custom_permissions' => [],

        'user_model' => \App\Models\User::class,

        'user_model_class' => 'User',

        'policies_namespace' => 'App\Policies',
    ],

    'layout' => [
        'resources' => [
            'default_section_column_span' => null,
        ],
    ],
];
