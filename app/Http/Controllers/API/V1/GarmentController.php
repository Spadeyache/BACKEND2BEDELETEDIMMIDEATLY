<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Garment;
use App\Traits\ApiResponse;
use App\Http\Requests\Api\V1\Garment\GarmentIndexRequest;
use App\Http\Resources\Api\V1\Garment\GarmentGetResource;
use App\Http\Resources\Api\V1\Garment\GarmentDetailsGetResource;

class GarmentController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/garments
     * - Accepts optional query param: ?category= (case-insensitive filter)
     * - Only returns garments where is_active = true
     * - Ordered by display_order ASC
     * - For each garment, also compute starting_price_cents: 
     *   MIN(price_cents) from garment_variants where garment_id matches and is_enabled = true
     * - Return fields: id, name, description, category, display_order, 
     *   print_area_specs, starting_price_cents
     */
    public function index(GarmentIndexRequest $request)
    {
        try {
            $data = $request->validated();
            $category = $data['category'] ?? null;

            $query = Garment::query()
                ->where('is_active', true)
                ->with(['garmentVariants' => function ($q) {
                    $q->where('is_enabled', true);
                }])
                ->withMin(['garmentVariants as starting_price_cents' => function ($q) {
                    $q->where('is_enabled', true);
                }], 'price_cents')
                ->orderBy('display_order', 'asc');

            if ($category !== null && $category !== '') {
                $query->whereRaw('LOWER(category) = ?', [strtolower($category)]);
            }

            $garments = $query->get([
                'id',
                'name',
                'description',
                'category',
                'display_order',
                'print_area_specs'
            ]);

            return $this->sendResponse(
                GarmentGetResource::collection($garments), 
                'Garments fetched successfully.', 
                200
            );
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * GET /api/garments/{id}
     * - Returns single garment where is_active = true, 404 otherwise
     * - Returns all garment fields including print_area_specs
     * - Eager loads garment_variants where is_enabled = true, ordered by display_order ASC
     * - Each variant returns: id, size, color, color_hex, blank_mockup_url, price_cents
     */
    public function show($id)
    {
        try {
            $garment = Garment::where('is_active', true)
                ->with(['garmentVariants' => function ($q) {
                    $q->where('is_enabled', true)->orderBy('display_order', 'asc');
                }])
                ->find($id);

            if (!$garment) {
                return $this->sendError('Garment not found.', [], 404);
            }

            return $this->sendResponse(
                new GarmentDetailsGetResource($garment), 
                'Garment details fetched successfully.', 
                200
            );
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
