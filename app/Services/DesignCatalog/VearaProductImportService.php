<?php

namespace App\Services\DesignCatalog;

use App\Models\DesignCatalogProduct;
use App\Models\DesignLabel;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VearaProductImportService
{
    public function importFromConnection(string $connection, int $limit = 100, bool $includeUnreviewed = false): array
    {
        $query = DB::connection($connection)
            ->table('ai_labeled_products as alp')
            ->join('scraped_products as sp', 'sp.id', '=', 'alp.product_id')
            ->select([
                'alp.id as source_labeled_product_id',
                'alp.product_id as source_product_id',
                'alp.design_labels',
                'alp.style_tags',
                'alp.color_palette',
                'alp.design_type',
                'alp.subject_matter',
                'alp.mood',
                'alp.complexity_score',
                'alp.text_present',
                'alp.placement',
                'alp.pet_relevance_score',
                'alp.target_audience_guess',
                'alp.seasonal_fit',
                'alp.front_design_url',
                'alp.back_design_url',
                'alp.label_confidence',
                'alp.review_source',
                'alp.proceed_to_vectorizing',
                'alp.vectorized',
                'alp.embedding',
                'alp.embedding_model',
                'alp.labeled_at',
                'sp.product_name',
                'sp.product_url',
                'sp.front_image_url',
                'sp.back_image_url',
                'sp.source_domain',
                'sp.price_range',
            ])
            ->orderBy('alp.labeled_at');

        if (!$includeUnreviewed) {
            $query->where('alp.proceed_to_vectorizing', true);
        }

        $rows = $query->limit($limit)->get();

        $imported = 0;
        $updated = 0;
        $attachedLabels = 0;

        foreach ($rows as $row) {
            $payload = $this->mapRow($row);

            $product = DesignCatalogProduct::updateOrCreate(
                ['source_labeled_product_id' => $payload['source_labeled_product_id']],
                $payload,
            );

            $product->wasRecentlyCreated ? $imported++ : $updated++;
            $attachedLabels += $this->syncLabels($product);
        }

        return [
            'read' => $rows->count(),
            'imported' => $imported,
            'updated' => $updated,
            'attached_labels' => $attachedLabels,
        ];
    }

    private function mapRow(object $row): array
    {
        $designLabels = $this->json($row->design_labels);
        $colorPalette = $this->json($row->color_palette);
        $vectorized = (bool) $row->vectorized;

        return [
            'source_product_id' => $row->source_product_id,
            'source_labeled_product_id' => $row->source_labeled_product_id,
            'source_system' => 'veara-design',
            'source_url' => $row->product_url,
            'source_domain' => $row->source_domain,
            'title' => $row->product_name,
            'front_image_url' => $row->front_design_url ?: $row->front_image_url,
            'back_image_url' => $row->back_design_url ?: $row->back_image_url,
            'price_range' => $row->price_range,
            'design_type' => $row->design_type,
            'mood' => $row->mood,
            'style_tags' => $this->array($row->style_tags),
            'subject_matter' => $this->array($row->subject_matter),
            'placement' => $this->array($row->placement),
            'target_audience_guess' => $this->array($row->target_audience_guess),
            'seasonal_fit' => $this->array($row->seasonal_fit),
            'color_palette' => $colorPalette,
            'design_labels' => $designLabels,
            'complexity_score' => $row->complexity_score,
            'text_present' => is_null($row->text_present) ? null : (bool) $row->text_present,
            'pet_relevance_score' => $row->pet_relevance_score,
            'label_confidence' => $row->label_confidence,
            'review_source' => $row->review_source,
            'vectorized' => $vectorized,
            'embedding' => $vectorized ? $this->vector($row->embedding) : null,
            'embedding_model' => $row->embedding_model,
            'labeled_at' => $row->labeled_at,
            'imported_at' => now(),
            'status' => $vectorized ? 'active' : 'draft',
        ];
    }

    private function syncLabels(DesignCatalogProduct $product): int
    {
        $pairs = collect([
            ['design_type', $product->design_type],
            ['mood', $product->mood],
        ]);

        foreach (['style_tags', 'subject_matter', 'placement', 'target_audience_guess', 'seasonal_fit'] as $field) {
            foreach (($product->{$field} ?? []) as $value) {
                $pairs->push([$field, $value]);
            }
        }

        $colorPalette = $product->color_palette ?? [];
        $pairs->push(['color_palette.colors', Arr::get($colorPalette, 'dominant')]);
        foreach ((array) Arr::get($colorPalette, 'accents', []) as $value) {
            $pairs->push(['color_palette.colors', $value]);
        }
        $pairs->push(['color_palette.brightness', Arr::get($colorPalette, 'brightness')]);
        $pairs->push(['color_palette.saturation', Arr::get($colorPalette, 'saturation')]);

        $designLabels = $product->design_labels ?? [];
        $pairs->push(['subject_matter', Arr::get($designLabels, 'primary_element')]);
        foreach ((array) Arr::get($designLabels, 'secondary_elements', []) as $value) {
            $pairs->push(['subject_matter', $value]);
        }
        $pairs->push(['design_labels.composition', Arr::get($designLabels, 'composition')]);
        $pairs->push(['design_labels.line_style', Arr::get($designLabels, 'line_style')]);

        $labels = $this->findLabels($pairs);
        DB::table('design_catalog_product_labels')->where('design_catalog_product_id', $product->id)->delete();

        $now = now();
        foreach ($labels as $label) {
            DB::table('design_catalog_product_labels')->updateOrInsert(
                [
                    'design_catalog_product_id' => $product->id,
                    'design_label_id' => $label->id,
                ],
                [
                    'group_key' => $label->group->key,
                    'label_key' => $label->key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        return $labels->count();
    }

    private function findLabels(Collection $pairs): Collection
    {
        $labels = collect();

        foreach ($pairs->filter(fn ($pair) => filled($pair[0]) && filled($pair[1])) as [$groupKey, $labelKey]) {
            $label = DesignLabel::query()
                ->where('key', $labelKey)
                ->whereHas('group', fn ($query) => $query->where('key', $groupKey))
                ->with('group')
                ->first();

            if ($label) {
                $labels->push($label);
            }
        }

        return $labels->unique('id')->values();
    }

    private function json(mixed $value): mixed
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: null;
        }

        return $value;
    }

    private function array(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && str_starts_with($value, '{')) {
            return array_values(array_filter(explode(',', trim($value, '{}')), 'strlen'));
        }

        return [];
    }

    private function vector(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return '[' . implode(',', $value) . ']';
        }

        return (string) $value;
    }
}
