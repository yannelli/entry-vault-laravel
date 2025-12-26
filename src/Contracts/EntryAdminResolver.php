<?php

namespace Yannelli\EntryVault\Contracts;

use Illuminate\Database\Eloquent\Model;

interface EntryAdminResolver
{
    /**
     * Determine if the given model has admin privileges.
     */
    public function isAdmin(Model $model): bool;

    /**
     * Determine if the given model can manage system categories.
     */
    public function canManageSystemCategories(Model $model): bool;

    /**
     * Determine if the given model can manage system templates.
     */
    public function canManageSystemTemplates(Model $model): bool;
}
