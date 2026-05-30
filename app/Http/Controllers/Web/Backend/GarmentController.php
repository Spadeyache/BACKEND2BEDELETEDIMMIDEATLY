<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Garment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class GarmentController extends Controller
{
    /**
     * List of garments
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Garment::latest();

            return DataTables::of($data)
                ->addColumn('name', fn($row) => $row->name)
                ->addColumn('category', fn($row) => $row->category)
                ->addColumn('blueprint_id', fn($row) => $row->blueprint_id)
                ->addColumn('display_order', fn($row) => $row->display_order)
                ->addColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-light-success">Active</span>'
                        : '<span class="badge badge-light-danger">Inactive</span>';
                })
                ->addColumn('created_at', fn($row) => $row->created_at ? $row->created_at->format('d M Y, h:i a') : '')
                ->addColumn('actions', function ($row) {
                    return '
                        <div class="d-flex justify-content-end">
                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                               data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                 data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="#" data-id="' . $row->id . '" class="menu-link edit-garment-btn px-3">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 delete-garment-btn" data-id="' . $row->id . '">Delete</a>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['is_active', 'actions'])
                ->make(true);
        }

        $token = config('services.printify.token');

        // Fetch blueprints from Printify API with 24 hours caching
        $blueprints = cache()->remember('printify_blueprints', 86400, function () use ($token) {
            $response = Http::withToken($token)
                ->get('https://api.printify.com/v1/catalog/blueprints.json');
            return $response->ok() ? $response->json() : [];
        });

        return view('backend.layout.Garment.index', compact('blueprints'));
    }

    /**
     * Store a new garment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'category'          => ['required', 'string', 'max:255'],
            'blueprint_id'      => ['required', 'integer'],
            'print_provider_id' => ['required', 'integer'],
            'display_order'     => ['required', 'integer', 'min:0'],
            'is_active'         => ['nullable', 'boolean'],
        ]);

        try {
            Garment::create([
                'name'              => $validated['name'],
                'description'       => $validated['description'] ?? null,
                'category'          => $validated['category'],
                'blueprint_id'      => $validated['blueprint_id'],
                'print_provider_id' => $validated['print_provider_id'],
                'display_order'     => $validated['display_order'],
                'is_active'         => isset($validated['is_active']) ? (bool) $validated['is_active'] : true,
            ]);

            return redirect()->back()->with('success', 'Garment created successfully.');
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Return garment data for edit modal
     */
    public function edit($id)
    {
        $garment = Garment::findOrFail($id);
        return response()->json(['garment' => $garment]);
    }

    /**
     * Update a garment
     */
    public function update(Request $request, $id)
    {
        try {
            $garment = Garment::findOrFail($id);

            $validated = $request->validate([
                'name'              => ['required', 'string', 'max:255'],
                'description'       => ['nullable', 'string'],
                'category'          => ['required', 'string', 'max:255'],
                'blueprint_id'      => ['required', 'integer'],
                'print_provider_id' => ['required', 'integer'],
                'display_order'     => ['required', 'integer', 'min:0'],
                'is_active'         => ['nullable'],
            ]);

            $garment->name              = $validated['name'];
            $garment->description       = $validated['description'] ?? null;
            $garment->category          = $validated['category'];
            $garment->blueprint_id      = $validated['blueprint_id'];
            $garment->print_provider_id = $validated['print_provider_id'];
            $garment->display_order     = $validated['display_order'];
            $garment->is_active         = isset($validated['is_active']) ? (bool) $validated['is_active'] : false;
            $garment->save();

            return response()->json(['message' => 'Garment updated successfully.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update garment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a garment
     */
    public function delete($id)
    {
        try {
            $garment = Garment::find($id);

            if (!$garment) {
                return response()->json(['status' => 'error', 'message' => 'Garment not found.'], 404);
            }

            $garment->delete();

            return response()->json(['status' => 'success', 'message' => 'Garment deleted successfully.']);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Get print providers for a given blueprint id from Printify
     */
    public function getPrintProviders($blueprint_id)
    {
        try {
            $token = config('services.printify.token');
            $cacheKey = "printify_blueprint_{$blueprint_id}_providers";
            
            $providers = cache()->remember($cacheKey, 86400, function () use ($token, $blueprint_id) {
                $response = Http::withToken($token)
                    ->get("https://api.printify.com/v1/catalog/blueprints/{$blueprint_id}/print_providers.json");
                return $response->ok() ? $response->json() : [];
            });

            return response()->json(['status' => 'success', 'providers' => $providers]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
