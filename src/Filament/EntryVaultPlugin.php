<?php

namespace Yannelli\EntryVault\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Yannelli\EntryVault\Filament\Resources\EntryCategoryResource;
use Yannelli\EntryVault\Filament\Resources\EntryResource;

class EntryVaultPlugin implements Plugin
{
    protected bool $hasEntryResource = true;

    protected bool $hasEntryCategoryResource = true;

    protected ?string $entryResourceClass = null;

    protected ?string $entryCategoryResourceClass = null;

    protected ?string $navigationGroup = null;

    protected ?int $navigationSort = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'entry-vault';
    }

    /**
     * Check if the Filament integration is enabled via config.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('entry-vault.filament.enabled', true);
    }

    public function register(Panel $panel): void
    {
        // Don't register any resources if Filament integration is disabled
        if (! static::isEnabled()) {
            return;
        }

        $resources = [];

        if ($this->hasEntryResource) {
            $resources[] = $this->entryResourceClass ?? EntryResource::class;
        }

        if ($this->hasEntryCategoryResource) {
            $resources[] = $this->entryCategoryResourceClass ?? EntryCategoryResource::class;
        }

        $panel->resources($resources);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function entryResource(bool $condition = true): static
    {
        $this->hasEntryResource = $condition;

        return $this;
    }

    public function entryCategoryResource(bool $condition = true): static
    {
        $this->hasEntryCategoryResource = $condition;

        return $this;
    }

    public function usingEntryResource(string $resource): static
    {
        $this->entryResourceClass = $resource;

        return $this;
    }

    public function usingEntryCategoryResource(string $resource): static
    {
        $this->entryCategoryResourceClass = $resource;

        return $this;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup ?? config('entry-vault.filament.navigation_group');
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort ?? config('entry-vault.filament.navigation_sort');
    }

    public function hasEntryResource(): bool
    {
        return $this->hasEntryResource;
    }

    public function hasEntryCategoryResource(): bool
    {
        return $this->hasEntryCategoryResource;
    }
}
