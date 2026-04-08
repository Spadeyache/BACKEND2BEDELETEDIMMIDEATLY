<?php

namespace App\Http\Resources\Api\V1\Products;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductGetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $design = Design::with('designImages')->where('user_id', $request->auth_user_id)->where('printify_product_id', $this['id'])->latest()->first();
        if(is_null($design)) {
            return [
                'id'                    => $this['id'],
                'design_id'             => null,
                'title'                 => $this['title'],
                'description'           => $this['description'],
                'variants'              => collect($this['variants'])->take(2)->map(function ($variants) {
                    return [
                        'id'    => $variants['id'],
                        'title' => $variants['title'],
                        'price' => $variants['price']
                    ];
                })->values()->all(),
                'front_and_back_images' => collect($this['images'])->take(2)->map(function ($image) {
                    return [
                        'position'  => $image['position'],
                        'src'       => $image['src']
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
            'variants'              => collect($this['variants'])->take(2)->map(function ($variants) {
                return [
                    'id'    => $variants['id'],
                    'title' => $variants['title'],
                    'price' => $variants['price']
                ];
            })->values()->all(),
            'front_and_back_images' => $design ? $design->designImages->map(function ($image) {
                return [
                    'position' => $image->area_name,
                    'src'      => $image->image_url,
                ];
            })->values()->all() : [],
            'mockup_image'          => $design->mockup_image ?? null,
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
