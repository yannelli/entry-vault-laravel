<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the database table names used by Entry Vault.
    |
    */
    'tables' => [
        'entries' => 'entries',
        'contents' => 'entry_contents',
        'categories' => 'entry_categories',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | Customize the model classes used by Entry Vault. You may extend the
    | default models and specify your custom classes here.
    |
    */
    'models' => [
        'entry' => \Yannelli\EntryVault\Models\Entry::class,
        'content' => \Yannelli\EntryVault\Models\EntryContent::class,
        'category' => \Yannelli\EntryVault\Models\EntryCategory::class,
        'version' => \Yannelli\EntryVault\Models\EntryVersion::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class for relationships like creator and updater.
    |
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Team Model
    |--------------------------------------------------------------------------
    |
    | The team model class for team-based ownership and visibility.
    | Set to null if your application doesn't use teams.
    |
    */
    'team_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Default Visibility
    |--------------------------------------------------------------------------
    |
    | The default visibility for newly created entries.
    | Options: 'public', 'private', 'team'
    |
    */
    'default_visibility' => 'private',

    /*
    |--------------------------------------------------------------------------
    | Default State
    |--------------------------------------------------------------------------
    |
    | The default state for newly created entries.
    | Options: 'draft', 'published', 'archived'
    |
    */
    'default_state' => 'draft',

    /*
    |--------------------------------------------------------------------------
    | Versioning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the versioning behavior for entries.
    |
    */
    'versioning' => [
        'enabled' => true,
        'strategy' => 'snapshot', // 'snapshot' or 'diff'
        'keep_versions' => 50,    // null for unlimited
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Types
    |--------------------------------------------------------------------------
    |
    | The allowed content types for entry content.
    |
    */
    'content_types' => [
        'markdown',
        'html',
        'json',
        'text',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Resolver
    |--------------------------------------------------------------------------
    |
    | A class implementing EntryAdminResolver to determine admin privileges.
    | Set to null to disable admin-specific features.
    |
    */
    'admin_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | Whether to use soft deletes for entries and categories.
    |
    */
    'soft_deletes' => true,

    /*
    |--------------------------------------------------------------------------
    | Seed Default Categories
    |--------------------------------------------------------------------------
    |
    | Whether to automatically seed default system categories on install.
    |
    */
    'seed_default_categories' => true,

    /*
    |--------------------------------------------------------------------------
    | Filament Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the optional Filament admin panel integration.
    | These settings are only used if you register the EntryVaultPlugin.
    |
    */
    'filament' => [
        /*
        |--------------------------------------------------------------------------
        | Navigation Group
        |--------------------------------------------------------------------------
        |
        | The navigation group for Entry Vault resources in the Filament sidebar.
        | Set to null to display resources at the top level.
        |
        */
        'navigation_group' => 'Content',

        /*
        |--------------------------------------------------------------------------
        | Navigation Sort
        |--------------------------------------------------------------------------
        |
        | The sort order for the Entry Vault navigation group.
        | Lower numbers appear first in the sidebar.
        |
        */
        'navigation_sort' => null,

        /*
        |--------------------------------------------------------------------------
        | Resource Labels
        |--------------------------------------------------------------------------
        |
        | Customize the labels used for Entry Vault resources in Filament.
        |
        */
        'entry_label' => 'Entry',
        'entry_plural_label' => 'Entries',
        'category_label' => 'Category',
        'category_plural_label' => 'Categories',
    ],
];
