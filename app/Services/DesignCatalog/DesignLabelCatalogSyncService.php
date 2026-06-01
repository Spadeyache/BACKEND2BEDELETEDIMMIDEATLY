<?php

namespace App\Services\DesignCatalog;

use App\Models\DesignLabel;
use App\Models\DesignLabelGroup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DesignLabelCatalogSyncService
{
    public function syncFromTaxonomyFile(string $path): array
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException("Taxonomy file not found: {$path}");
        }

        $taxonomy = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $groups = [];

        foreach (($taxonomy['fields'] ?? []) as $key => $definition) {
            $groups[] = [
                'key' => $key,
                'kind' => $definition['kind'] ?? null,
                'values' => $definition['values'] ?? [],
            ];
        }

        $groups[] = [
            'key' => 'color_palette.colors',
            'kind' => 'multi',
            'values' => Arr::get($taxonomy, 'structured.color_palette.colors.values', []),
        ];
        $groups[] = [
            'key' => 'color_palette.brightness',
            'kind' => 'single',
            'values' => Arr::get($taxonomy, 'structured.color_palette.brightness.values', []),
        ];
        $groups[] = [
            'key' => 'color_palette.saturation',
            'kind' => 'single',
            'values' => Arr::get($taxonomy, 'structured.color_palette.saturation.values', []),
        ];
        $groups[] = [
            'key' => 'design_labels.composition',
            'kind' => 'single',
            'values' => Arr::get($taxonomy, 'structured.design_labels.composition.values', []),
        ];
        $groups[] = [
            'key' => 'design_labels.line_style',
            'kind' => 'single',
            'values' => Arr::get($taxonomy, 'structured.design_labels.line_style.values', []),
        ];

        $syncedGroups = 0;
        $syncedLabels = 0;

        foreach ($groups as $index => $definition) {
            if (empty($definition['key'])) {
                continue;
            }

            $group = DesignLabelGroup::updateOrCreate(
                ['key' => $definition['key']],
                [
                    'name' => $this->humanize($definition['key']),
                    'kind' => $definition['kind'] ?? null,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
            $syncedGroups++;

            foreach (array_values($definition['values'] ?? []) as $labelIndex => $value) {
                DesignLabel::updateOrCreate(
                    [
                        'design_label_group_id' => $group->id,
                        'key' => $value,
                    ],
                    [
                        'value' => $value,
                        'name' => $this->humanize($value),
                        'sort_order' => $labelIndex,
                        'is_active' => true,
                    ],
                );
                $syncedLabels++;
            }
        }

        return [
            'groups' => $syncedGroups,
            'labels' => $syncedLabels,
        ];
    }

    private function humanize(string $value): string
    {
        return Str::of($value)
            ->replace(['.', '_', '-'], ' ')
            ->title()
            ->toString();
    }
}
