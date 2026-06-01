<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DesignCatalog\DesignLabelGroupResource;
use App\Http\Resources\Api\V1\DesignCatalog\VearaProductResource;
use App\Models\DesignLabelGroup;
use App\Models\VearaProduct;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DesignCatalogController extends Controller
{
    use ApiResponse;

    public function products(Request $request)
    {
        try {
            $query = VearaProduct::query()
                ->with('labels.group')
                ->where('status', $request->get('status', 'active'));

            if ($request->filled('design_type')) {
                $query->where('design_type', $request->design_type);
            }

            if ($request->filled('mood')) {
                $query->where('mood', $request->mood);
            }

            if ($request->filled('source_domain')) {
                $query->where('source_domain', $request->source_domain);
            }

            if ($request->filled('label')) {
                $labels = collect(explode(',', $request->label))
                    ->map(fn ($value) => trim($value))
                    ->filter();

                foreach ($labels as $label) {
                    $query->whereHas('labels', fn ($labelsQuery) => $labelsQuery->where('design_labels.key', $label));
                }
            }

            $products = $query
                ->latest('imported_at')
                ->paginate((int) $request->get('per_page', 24));

            return $this->sendResponse(
                VearaProductResource::collection($products),
                'Veara products retrieved successfully',
                200,
                null,
                $products,
            );
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function product(string $id)
    {
        try {
            $product = VearaProduct::with('labels.group')->findOrFail($id);

            return $this->sendResponse(new VearaProductResource($product), 'Veara product retrieved successfully', 200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function labels()
    {
        try {
            $groups = DesignLabelGroup::with(['labels' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return $this->sendResponse(DesignLabelGroupResource::collection($groups), 'Design labels retrieved successfully', 200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
