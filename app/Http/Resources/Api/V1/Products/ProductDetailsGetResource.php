<?php

namespace App\Http\Resources\Api\V1\Products;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsGetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $design = Design::with('designImages')->where('user_id', auth()->id())->where('printify_product_id', $this['id'])->latest()->first();
        if(is_null($design)) {
            return [
                'id'                    => $this['id'],
                'design_id'             => null,
                'title'                 => $this['title'],
                'description'           => $this['description'],
                'front_and_back_images' => collect($this['images'])->take(2)->map(function ($image) {
                    return [
                        'position'  => $image['position'],
                        'src'       => $image['src']
                    ];
                })->values()->all(),
                'colors'                => collect(
                                        collect($this['options'])
                                            ->where('type', 'color')
                                            ->first()['values'])->take(10)->values(),
                'sizes'                 => collect(
                                        collect($this['options'])
                                            ->where('type', 'size')
                                            ->first()['values'])->values(),
                'variants'              => collect($this['variants'])->take(10)->map(function ($variants) {
                    return [
                        'id'    => $variants['id'],
                        'title' => $variants['title'],
                        'price' => $variants['price']
                    ];
                })->values()->all(),
                'views'                 => collect($this['views'])->map(function ($view) {
                    return [
                        'id'       => $view['id'],
                        'label'    => $view['label'],
                        'position' => $view['position'],
                        'src'      => $view['files'][0]['src'] ?? null,
                    ];
                })->values()->all(),
                'price'                 => $this['variants'][0]['price'],
                'tags'                  => $this['tags'],
                'created_at'            => $this['created_at'],
                'updated_at'            => $this['updated_at'],
            ];
        }
        return [
            'id'                    => $this['id'],
            'design_id'             => $design->id,
            'title'                 => $this['title'] . ' (custom)',
            'description'           => $this['description'],
            'front_and_back_images' => $design ? $design->designImages->map(function ($image) {
                return [
                    'position' => $image->area_name,
                    'src'      => $image->image_url,
                ];
            })->values()->all() : [],
            'mockup_image'          => $design->mockup_image ?? null,
            'colors'                => collect(
                                    collect($this['options'])
                                        ->where('type', 'color')
                                        ->first()['values'])->take(10)->values(),
            'sizes'                 => collect(
                                    collect($this['options'])
                                        ->where('type', 'size')
                                        ->first()['values'])->values(),
            'variants'              => collect($this['variants'])->take(10)->map(function ($variants) {
                return [
                    'id'    => $variants['id'],
                    'title' => $variants['title'],
                    'price' => $variants['price']
                ];
            })->values()->all(),
            'views'                 => collect($this['views'])->map(function ($view) {
                return [
                    'id'       => $view['id'],
                    'label'    => $view['label'],
                    'position' => $view['position'],
                    'src'      => $view['files'][0]['src'] ?? null,
                ];
            })->values()->all(),
            'price'                 => $this['variants'][0]['price'],
            'tags'                  => $this['tags'],
            'created_at'            => $this['created_at'],
            'updated_at'            => $this['updated_at'],
        ];
    }
}
