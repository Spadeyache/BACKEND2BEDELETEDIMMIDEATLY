<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\VearaProducts;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class VearaProductController extends Controller
{
    /**
     * list of veara products
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = VearaProducts::latest();

            return DataTables::of($data)
                ->addColumn('product_id', function ($row) {
                    return $row->product_id;
                })
                ->addColumn('design_type', function ($row) {
                    return $row->design_type;
                })
                ->addColumn('category', function ($row) {
                    return $row->category;
                })
                ->addColumn('veara_front', function ($row) {
                    $imgUrl = $row->veara_front ? Helpers::generateTempURL($row->veara_front, config('app.file_system')) : asset('backend/assets/media/avatars/300-1.jpg');
                    return '<div class="symbol symbol-50px me-3"><img src="' . $imgUrl . '" class="" alt=""></div>';
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
                                        <a href="#" data-id="' . $row->id . '" class="menu-link edit-product-btn px-3">Edit</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 delete-product-btn" data-id="' . $row->id . '">Delete</a>
                                    </div>
                                </div>
                            </div>
                            ';
                })
                ->rawColumns(['veara_front', 'actions'])
                ->make(true);
        }

        // ONLY when not ajax
        return view('backend.layout.VearaProduct.index');
    }

    /**
     * store veara product data
     */
    public function store(Request $request)
    {
        $imageValidation = function ($attribute, $value, $fail) {
            if (!$value instanceof UploadedFile) {
                return;
            }
            $extension = strtolower($value->getClientOriginalExtension());
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'webp', 'svg'];
            if (!in_array($extension, $allowedExtensions)) {
                $fail("The " . str_replace('_', ' ', $attribute) . " must be a file of type: " . implode(', ', $allowedExtensions) . ".");
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
            'product_id'            => ['nullable', 'string', 'max:255'],
            'title'                 => ['required', 'string', 'max:255'],
            'price'                 => ['required', 'numeric', 'min:0'],
            'description'           => ['nullable', 'string'],
            'veara_front'           => ['required', 'file', 'max:5120', $imageValidation],
            'veara_back'            => ['required', 'file', 'max:5120', $imageValidation],
            'front_mockup'          => ['required', 'file', 'max:5120', $imageValidation],
            'back_mockup'           => ['required', 'file', 'max:5120', $imageValidation],
            'style_tags'            => ['required', 'string'],
            'color_palette'         => ['required'], 
            'design_type'           => ['required', 'string', 'max:255'],
            'category'              => ['required', 'string', 'max:255'],
            'subject_matter'        => ['required', 'string'],
            'mood'                  => ['required', 'string', 'max:255'],
            'complexity_score'      => ['required', 'integer'],
            'pet_relevance_score'   => ['required', 'numeric'],
            'target_audience_guess' => ['required', 'string'],
            'seasonal_fit'          => ['required', 'string'],
        ]);

        try {
            $imagePaths = [];
            $imageFields = ['veara_front', 'veara_back', 'front_mockup', 'back_mockup'];

            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $mediaFile = time() . Str::random(10) . '_' . $field . '.' . $file->getClientOriginalExtension();
                    $imagePaths[$field] = Helpers::uploadFile($field, $file, $mediaFile, config('app.file_system'));
                }
            }

            $colorPalette = is_array($validated['color_palette']) 
                ? json_encode($validated['color_palette']) 
                : $validated['color_palette'];

            VearaProducts::create([
                'product_id'            => $validated['product_id'],
                'title'                 => $validated['title'],
                'price'                 => $validated['price'],
                'description'           => $validated['description'] ?? null,
                'veara_front'           => $imagePaths['veara_front'] ?? null,
                'veara_back'            => $imagePaths['veara_back'] ?? null,
                'front_mockup'          => $imagePaths['front_mockup'] ?? null,
                'back_mockup'           => $imagePaths['back_mockup'] ?? null,
                'style_tags'            => $validated['style_tags'],
                'color_palette'         => $colorPalette,
                'design_type'           => $validated['design_type'],
                'category'              => $validated['category'],
                'subject_matter'        => $validated['subject_matter'],
                'mood'                  => $validated['mood'],
                'complexity_score'      => $validated['complexity_score'],
                'pet_relevance_score'   => $validated['pet_relevance_score'],
                'target_audience_guess' => $validated['target_audience_guess'],
                'seasonal_fit'          => $validated['seasonal_fit'],
                'embedding_model'       => 'random text',
                'labeled_at'            => now(),
            ]);

            return redirect()->back()->with('success', 'Veara Product Created Successfully');
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * show edit data
     */
    public function edit($id)
    {
        $product = VearaProducts::findOrFail($id);
        
        $imageFields = ['veara_front', 'veara_back', 'front_mockup', 'back_mockup'];
        foreach ($imageFields as $field) {
            if ($product->$field) {
                $product->$field = Helpers::generateTempURL($product->$field, config('app.file_system'));
            }
        }
        
        return response()->json(['product' => $product]);
    }

    /**
     * veara product data update
     */
    public function update(Request $request, $id)
    {
        try {
            $product = VearaProducts::findOrFail($id);

            $imageValidation = function ($attribute, $value, $fail) {
                if (!$value instanceof UploadedFile) {
                    return;
                }
                $extension = strtolower($value->getClientOriginalExtension());
                $allowedExtensions = ['jpeg', 'png', 'jpg', 'webp', 'svg'];
                if (!in_array($extension, $allowedExtensions)) {
                    $fail("The " . str_replace('_', ' ', $attribute) . " must be a file of type: " . implode(', ', $allowedExtensions) . ".");
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
                'product_id'            => ['nullable', 'string', 'max:255'],
                'title'                 => ['required', 'string', 'max:255'],
                'price'                 => ['required', 'numeric', 'min:0'],
                'description'           => ['nullable', 'string'],
                'veara_front'           => ['nullable', 'file', 'max:5120', $imageValidation],
                'veara_back'            => ['nullable', 'file', 'max:5120', $imageValidation],
                'front_mockup'          => ['nullable', 'file', 'max:5120', $imageValidation],
                'back_mockup'           => ['nullable', 'file', 'max:5120', $imageValidation],
                'style_tags'            => ['required', 'string'],
                'color_palette'         => ['required'], 
                'design_type'           => ['required', 'string', 'max:255'],
                'category'              => ['required', 'string', 'max:255'],
                'subject_matter'        => ['required', 'string'],
                'mood'                  => ['required', 'string', 'max:255'],
                'complexity_score'      => ['required', 'integer'],
                'pet_relevance_score'   => ['required', 'numeric'],
                'target_audience_guess' => ['required', 'string'],
                'seasonal_fit'          => ['required', 'string'],
            ]);

            $product->product_id            = $validated['product_id'];
            $product->title                 = $validated['title'];
            $product->price                 = $validated['price'];
            $product->description           = $validated['description'] ?? null;
            $product->style_tags            = $validated['style_tags'];
            $product->color_palette         = is_array($validated['color_palette']) ? json_encode($validated['color_palette']) : $validated['color_palette'];
            $product->design_type           = $validated['design_type'];
            $product->category              = $validated['category'];
            $product->subject_matter        = $validated['subject_matter'];
            $product->mood                  = $validated['mood'];
            $product->complexity_score      = $validated['complexity_score'];
            $product->pet_relevance_score   = $validated['pet_relevance_score'];
            $product->target_audience_guess = $validated['target_audience_guess'];
            $product->seasonal_fit          = $validated['seasonal_fit'];

            $imageFields = ['veara_front', 'veara_back', 'front_mockup', 'back_mockup'];
            foreach ($imageFields as $field) {
                if ($request->hasFile($field)) {
                    if ($product->$field) {
                        Helpers::deleteFile($product->$field, config('app.file_system'));
                    }
                    $file = $request->file($field);
                    $mediaFile = time() . Str::random(10) . '_' . $field . '.' . $file->getClientOriginalExtension();
                    $product->$field = Helpers::uploadFile($field, $file, $mediaFile, config('app.file_system'));
                } elseif ($request->input($field . '_remove')) {
                    if ($product->$field) {
                        Helpers::deleteFile($product->$field, config('app.file_system'));
                    }
                    $product->$field = null;
                }
            }

            $product->save();

            return response()->json(['message' => 'Product updated successfully'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed: ' . $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * delete veara product
     */
    public function delete($id)
    {
        try {
            $product = VearaProducts::find($id);

            if (!$product) {
                return response()->json(['status' => 'error', 'message' => 'Product not found.'], 404);
            }

            $imageFields = ['veara_front', 'veara_back', 'front_mockup', 'back_mockup'];
            foreach ($imageFields as $field) {
                if ($product->$field) {
                    Helpers::deleteFile($product->$field, config('app.file_system'));
                }
            }

            $product->delete();

            return response()->json(['status' => 'success', 'message' => 'Product deleted successfully.']);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }
}

