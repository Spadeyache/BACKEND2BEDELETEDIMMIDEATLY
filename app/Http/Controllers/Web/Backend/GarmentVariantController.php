<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Helpers\Helpers;
use App\Models\Garment;
use App\Models\GarmentVariant;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class GarmentVariantController extends Controller
{
    /**
     * List of garment variants
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = GarmentVariant::with('garment')->latest();

            return DataTables::of($data)
                ->addColumn('garment_name', fn($row) => $row->garment?->name ?? '—')
                ->addColumn('size', fn($row) => $row->size)
                ->addColumn('color', function ($row) {
                    $hex = $row->color_hex ?? '#cccccc';
                    
                    // Strip the hash if present
                    $hexClean = str_replace('#', '', $hex);
                    
                    // Expand 3-character hex to 6-character
                    if (strlen($hexClean) === 3) {
                        $hexClean = $hexClean[0] . $hexClean[0] . $hexClean[1] . $hexClean[1] . $hexClean[2] . $hexClean[2];
                    }
                    
                    $textColor = '#000000';
                    if (strlen($hexClean) === 6) {
                        $r = hexdec(substr($hexClean, 0, 2));
                        $g = hexdec(substr($hexClean, 2, 2));
                        $b = hexdec(substr($hexClean, 4, 2));
                        
                        // YIQ contrast formula to determine light vs dark
                        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                        if ($yiq < 128) {
                            $textColor = '#ffffff';
                        }
                    }
                    
                    // Add a subtle border for light colored badges to keep them defined
                    $border = ($textColor === '#000000') ? 'border: 1px solid #e1e3ea;' : '';
                    
                    return '<span class="badge" style="background-color:' . htmlspecialchars($hex) . '; color:' . $textColor . '; padding: 4px 10px; ' . $border . '">'
                        . htmlspecialchars($row->color)
                        . '</span>';
                })
                ->addColumn('blank_mockup_url', function ($row) {
                    $imgUrl = $row->blank_mockup_url
                        ? Helpers::generateTempURL($row->blank_mockup_url, config('app.file_system'))
                        : asset('backend/assets/media/avatars/300-1.jpg');
                    return '<div class="symbol symbol-50px me-3"><img src="' . $imgUrl . '" class="" alt=""></div>';
                })
                ->addColumn('price_cents', fn($row) => '$' . number_format($row->price_cents / 100, 2))
                ->addColumn('is_enabled', function ($row) {
                    return $row->is_enabled
                        ? '<span class="badge badge-light-success">Enabled</span>'
                        : '<span class="badge badge-light-danger">Disabled</span>';
                })
                ->addColumn('display_order', fn($row) => $row->display_order)
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
                                    <a href="#" data-id="' . $row->id . '" class="menu-link edit-variant-btn px-3">Edit</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 delete-variant-btn" data-id="' . $row->id . '">Delete</a>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['color', 'blank_mockup_url', 'is_enabled', 'actions'])
                ->make(true);
        }

        $garments = Garment::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        return view('backend.layout.GarmentVariant.index', compact('garments'));
    }

    /**
     * Store a new garment variant
     */
    public function store(Request $request)
    {
        $imageValidation = function ($attribute, $value, $fail) {
            if (!$value instanceof UploadedFile) return;
            $extension = strtolower($value->getClientOriginalExtension());
            $allowed   = ['jpeg', 'png', 'jpg', 'webp', 'svg'];
            if (!in_array($extension, $allowed)) {
                $fail("The " . str_replace('_', ' ', $attribute) . " must be a file of type: " . implode(', ', $allowed) . ".");
                return;
            }
            if ($extension === 'svg') {
                $content = @file_get_contents($value->getRealPath());
                if ($content === false || strpos($content, '<svg') === false) {
                    $fail("The " . str_replace('_', ' ', $attribute) . " must be a valid SVG file.");
                }
            } else {
                if (@getimagesize($value->getRealPath()) === false) {
                    $fail("The " . str_replace('_', ' ', $attribute) . " must be an image.");
                }
            }
        };

        $validated = $request->validate([
            'garment_id'          => ['required', 'integer', 'exists:garments,id'],
            'printify_variant_id' => ['required', 'string', 'max:255'],
            'size'                => ['required', 'string', 'max:255'],
            'color'               => ['required', 'string', 'max:255'],
            'color_hex'           => ['nullable', 'string', 'max:20'],
            'blank_mockup_url'    => ['required', 'file', 'max:5120', $imageValidation],
            'price_cents'         => ['required', 'integer', 'min:0'],
            'is_enabled'          => ['nullable', 'boolean'],
            'display_order'       => ['required', 'integer', 'min:0'],
        ]);

        try {
            $mockupPath = null;
            if ($request->hasFile('blank_mockup_url')) {
                $file      = $request->file('blank_mockup_url');
                $mediaFile = time() . Str::random(10) . '_blank_mockup.' . $file->getClientOriginalExtension();
                $mockupPath = Helpers::uploadFile('blank_mockup_url', $file, $mediaFile, config('app.file_system'));
            }

            GarmentVariant::create([
                'garment_id'          => $validated['garment_id'],
                'printify_variant_id' => $validated['printify_variant_id'],
                'size'                => $validated['size'],
                'color'               => $validated['color'],
                'color_hex'           => $validated['color_hex'] ?? null,
                'blank_mockup_url'    => $mockupPath,
                'price_cents'         => $validated['price_cents'],
                'is_enabled'          => isset($validated['is_enabled']) ? (bool) $validated['is_enabled'] : true,
                'display_order'       => $validated['display_order'],
            ]);

            return redirect()->back()->with('success', 'Garment Variant created successfully.');
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Return garment variant data for edit modal
     */
    public function edit($id)
    {
        $variant = GarmentVariant::findOrFail($id);

        if ($variant->blank_mockup_url) {
            $variant->blank_mockup_url_preview = Helpers::generateTempURL($variant->blank_mockup_url, config('app.file_system'));
        }

        return response()->json(['variant' => $variant]);
    }

    /**
     * Update a garment variant
     */
    public function update(Request $request, $id)
    {
        try {
            $variant = GarmentVariant::findOrFail($id);

            $imageValidation = function ($attribute, $value, $fail) {
                if (!$value instanceof UploadedFile) return;
                $extension = strtolower($value->getClientOriginalExtension());
                $allowed   = ['jpeg', 'png', 'jpg', 'webp', 'svg'];
                if (!in_array($extension, $allowed)) {
                    $fail("The " . str_replace('_', ' ', $attribute) . " must be a file of type: " . implode(', ', $allowed) . ".");
                    return;
                }
                if ($extension === 'svg') {
                    $content = @file_get_contents($value->getRealPath());
                    if ($content === false || strpos($content, '<svg') === false) {
                        $fail("The " . str_replace('_', ' ', $attribute) . " must be a valid SVG file.");
                    }
                } else {
                    if (@getimagesize($value->getRealPath()) === false) {
                        $fail("The " . str_replace('_', ' ', $attribute) . " must be an image.");
                    }
                }
            };

            $validated = $request->validate([
                'garment_id'          => ['required', 'integer', 'exists:garments,id'],
                'printify_variant_id' => ['required', 'string', 'max:255'],
                'size'                => ['required', 'string', 'max:255'],
                'color'               => ['required', 'string', 'max:255'],
                'color_hex'           => ['nullable', 'string', 'max:20'],
                'blank_mockup_url'    => ['nullable', 'file', 'max:5120', $imageValidation],
                'price_cents'         => ['required', 'integer', 'min:0'],
                'is_enabled'          => ['nullable'],
                'display_order'       => ['required', 'integer', 'min:0'],
            ]);

            $variant->garment_id          = $validated['garment_id'];
            $variant->printify_variant_id = $validated['printify_variant_id'];
            $variant->size                = $validated['size'];
            $variant->color               = $validated['color'];
            $variant->color_hex           = $validated['color_hex'] ?? null;
            $variant->price_cents         = $validated['price_cents'];
            $variant->is_enabled          = isset($validated['is_enabled']) ? (bool) $validated['is_enabled'] : false;
            $variant->display_order       = $validated['display_order'];

            if ($request->hasFile('blank_mockup_url')) {
                if ($variant->blank_mockup_url) {
                    Helpers::deleteFile($variant->blank_mockup_url, config('app.file_system'));
                }
                $file      = $request->file('blank_mockup_url');
                $mediaFile = time() . Str::random(10) . '_blank_mockup.' . $file->getClientOriginalExtension();
                $variant->blank_mockup_url = Helpers::uploadFile('blank_mockup_url', $file, $mediaFile, config('app.file_system'));
            } elseif ($request->input('blank_mockup_url_remove')) {
                if ($variant->blank_mockup_url) {
                    Helpers::deleteFile($variant->blank_mockup_url, config('app.file_system'));
                }
                $variant->blank_mockup_url = null;
            }

            $variant->save();

            return response()->json(['message' => 'Garment Variant updated successfully.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update variant: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a garment variant
     */
    public function delete($id)
    {
        try {
            $variant = GarmentVariant::find($id);

            if (!$variant) {
                return response()->json(['status' => 'error', 'message' => 'Garment Variant not found.'], 404);
            }

            if ($variant->blank_mockup_url) {
                Helpers::deleteFile($variant->blank_mockup_url, config('app.file_system'));
            }

            $variant->delete();

            return response()->json(['status' => 'success', 'message' => 'Garment Variant deleted successfully.']);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    /**
     * Get Printify Variants for a garment
     */
    public function getPrintifyVariants($id)
    {
        try {
            $garment = Garment::findOrFail($id);

            if (!$garment->blueprint_id || !$garment->print_provider_id) {
                return response()->json(['status' => 'error', 'message' => 'Garment does not have blueprint or provider IDs'], 400);
            }

            $url = "https://api.printify.com/v1/catalog/blueprints/{$garment->blueprint_id}/print_providers/{$garment->print_provider_id}/variants.json";
            $response = Http::withToken(env('PRINTIFY_BEARER_TOKEN'))
                ->get($url);

            if ($response->ok()) {
                $data = $response->json();
                $variants = [];
                foreach ($data['variants'] ?? [] as $variant) {
                    $variants[] = [
                        'id' => $variant['id'],
                        'title' => $variant['title']
                    ];
                }
                return response()->json(['status' => 'success', 'variants' => $variants]);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to fetch variants from Printify.'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
