<?php

namespace Yannelli\EntryVault\Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DefaultCategoriesSeeder
{
    protected ?Command $command = null;

    protected array $categories = [
        [
            'name' => 'General',
            'slug' => 'general',
            'description' => 'General purpose entries',
            'icon' => 'heroicon-o-document-text',
            'color' => 'gray',
            'is_default' => true,
            'display_order' => 0,
        ],
        [
            'name' => 'Onboarding',
            'slug' => 'onboarding',
            'description' => 'Onboarding resources and guides',
            'icon' => 'heroicon-o-academic-cap',
            'color' => 'blue',
            'is_default' => false,
            'display_order' => 1,
        ],
        [
            'name' => 'Getting Started',
            'slug' => 'getting-started',
            'description' => 'Getting started guides and tutorials',
            'icon' => 'heroicon-o-rocket-launch',
            'color' => 'green',
            'is_default' => false,
            'display_order' => 2,
        ],
        [
            'name' => 'Templates',
            'slug' => 'templates',
            'description' => 'Reusable templates and starting points',
            'icon' => 'heroicon-o-document-duplicate',
            'color' => 'purple',
            'is_default' => false,
            'display_order' => 3,
        ],
        [
            'name' => 'Help & Support',
            'slug' => 'help-support',
            'description' => 'Help articles and support resources',
            'icon' => 'heroicon-o-question-mark-circle',
            'color' => 'yellow',
            'is_default' => false,
            'display_order' => 4,
        ],
    ];

    public function setCommand(Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function run(bool $force = false): void
    {
        $categoryModel = config('entry-vault.models.category');

        foreach ($this->categories as $categoryData) {
            $exists = $categoryModel::where('slug', $categoryData['slug'])
                ->whereNull('owner_type')
                ->whereNull('owner_id')
                ->exists();

            if ($exists && ! $force) {
                $this->info("Category '{$categoryData['name']}' already exists, skipping...");

                continue;
            }

            if ($exists && $force) {
                $categoryModel::where('slug', $categoryData['slug'])
                    ->whereNull('owner_type')
                    ->whereNull('owner_id')
                    ->delete();
            }

            $categoryModel::create([
                'uuid' => (string) Str::uuid(),
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
                'description' => $categoryData['description'],
                'icon' => $categoryData['icon'],
                'color' => $categoryData['color'],
                'is_system' => true,
                'is_default' => $categoryData['is_default'],
                'owner_type' => null,
                'owner_id' => null,
                'display_order' => $categoryData['display_order'],
            ]);

            $this->info("Created category: {$categoryData['name']}");
        }
    }

    protected function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }
}
