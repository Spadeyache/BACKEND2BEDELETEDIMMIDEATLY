<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Design\DesignSaveRequest;
use App\Http\Resources\Api\V1\Design\DesignGetResource;
use App\Http\Resources\Api\V1\Design\DesignRenderGetResource;
use App\Http\Resources\Api\V1\DesignPageGetResource;
use App\Http\Resources\Api\V1\Garment\GarmentVariantResource;
use App\Models\Design;
use App\Models\DesignElements;
use App\Models\DesignRender;
use App\Models\Garment;
use App\Models\GarmentVariant;
use App\Models\VearaProducts;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignController extends Controller
{
    use ApiResponse;
    //
    public function index()
    {
        try {
            $user_id = auth()->id();

            $designs = Design::with('designImages')->where('user_id', $user_id)->latest()->get();

            return $this->sendResponse(DesignPageGetResource::collection($designs),'Product design saved successfully',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function designVariants($id)
    {
        try {
            $veara_product = VearaProducts::findOrFail($id);

            $garments = Garment::where('category', $veara_product->category)->first();
            $garment_variants = GarmentVariant::where('garment_id', $garments->id)->where('is_enabled', true)->latest()->get();

            return $this->sendResponse(GarmentVariantResource::collection($garment_variants),'Design Variants data retrieved',200);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function saveDesign(DesignSaveRequest $request)
    {
        try {
            $user_id = auth()->id();
            $data = $request->validated();
            
            DB::beginTransaction();
            $design = null;
            $design_id = null;

            // Determine if this is an update by checking for design_render_id in the elements
            $elementsFieldsToCheck = ['front_elements', 'back_elements'];
            foreach ($elementsFieldsToCheck as $elementsField) {
                if (isset($data[$elementsField]) && is_array($data[$elementsField])) {
                    foreach ($data[$elementsField] as $element) {
                        if (!empty($element['design_render_id'])) {
                            $render = DesignRender::find($element['design_render_id']);
                            if ($render) {
                                $design_id = $render->design_id;
                                break 2;
                            }
                        }
                    }
                }
            }

            if ($design_id) {
                $design = Design::find($design_id);
                if ($design) {
                    $design->update([
                        'mockup_image' => $data['full_mockup'],
                    ]);
                }
            }
            
            if (!$design) {
                $design = Design::create([
                    'user_id'               => $user_id,
                    'veara_product_id'      => $data['veara_product_id'] ?? null,
                    // 'printify_product_id'   => $data['printify_product_id'] ?? null,
                    'mockup_image'          => $data['full_mockup'],
                    'print_files'           => $data['print_files'] ?? null,
                    'created_by'            => $user_id
                ]);
            }
            
            $imageFields = ['front_image', 'back_image'];

            $designRenders = [];

            foreach ($imageFields as $field) {
                if ($request->filled($field)) {
                    $areaName = str_replace('_image', '', $field); // "front" or "back"
                    $elementsField = $areaName . '_elements';

                    $design_render_id = null;
                    if (isset($data[$elementsField]) && is_array($data[$elementsField])) {
                        foreach ($data[$elementsField] as $element) {
                            if (!empty($element['design_render_id'])) {
                                $design_render_id = $element['design_render_id'];
                                break;
                            }
                        }
                    }

                    $designRender = null;
                    if ($design_render_id) {
                        $designRender = DesignRender::find($design_render_id);
                        if ($designRender) {
                            $designRender->update([
                                'image_url' => $data[$field],
                            ]);
                        }
                    }
                    
                    if (!$designRender) {
                        $designRender = DesignRender::create([
                            'design_id'  => $design->id,
                            'area_name'  => $areaName,
                            'image_url'  => $data[$field],
                            'created_by' => $user_id,
                        ]);
                    }
                    
                    $designRenders[$field] = $designRender;

                    // Check if there are any elements passed for this area
                    if (isset($data[$elementsField]) && is_array($data[$elementsField])) {
                        foreach ($data[$elementsField] as $element) {
                            $elementData = [
                                'design_render_id' => $designRender->id,
                                'design_labels'    => $element['design_labels'] ?? [],
                                'type'             => $element['type'] ?? 'image',
                                'content'          => $element['content'] ?? '',
                                'placement'        => $element['placement'] ?? 'center',
                                'x_position'       => $element['x_position'] ?? 0,
                                'y_position'       => $element['y_position'] ?? 0,
                                'width'            => $element['width'] ?? 0,
                                'height'           => $element['height'] ?? 0,
                                'scale'            => $element['scale'] ?? null,
                                'angle'            => $element['angle'] ?? null,
                                'font_family'      => $element['font_family'] ?? null,
                                'font_size'        => $element['font_size'] ?? null,
                                'color'            => $element['color'] ?? '',
                                'status'           => $element['status'] ?? 'active',
                                'created_by'       => $user_id,
                            ];

                            if (!empty($element['id'])) {
                                $designElement = DesignElements::find($element['id']);
                                if ($designElement) {
                                    $designElement->update($elementData);
                                } else {
                                    DesignElements::create($elementData);
                                }
                            } else {
                                DesignElements::create($elementData);
                            }
                        }
                    }
                }
            }
            
            DB::commit();

            $renderResources = [];
            foreach ($designRenders as $field => $render) {
                $key = str_replace('_image', '', $field) . '_design'; // "front_image" -> "front_design"
                $renderResources[$key] = new DesignRenderGetResource($render);
            }

            $datas = array_merge(
                ['design' => new DesignGetResource($design)],
                $renderResources
            );

            return $this->sendResponse($datas, 'Product design saved successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getDesignDetails($id)
    {
        try {
            $design = Design::findOrFail($id);
            $design_renders = DesignRender::where('design_id', $id)->get();
            $render_ids = $design_renders->pluck('id')->toArray();
            $design_elements = DesignElements::whereIn('design_render_id', $render_ids)->get();

            return $this->sendResponse([
                'design' => $design,
                'design_renders' => $design_renders,
                'design_elements' => $design_elements
            ], 'Design details fetched successfully', 200);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Design not found', [], 404);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    public function deleteDesign($id)
    {
        try {
            $design = Design::findOrFail($id);
            $design_renders = DesignRender::where('design_id', $id)->get();
            $render_ids = $design_renders->pluck('id')->toArray();
            $design_elements = DesignElements::whereIn('design_render_id', $render_ids)->get();
            $element_ids = $design_elements->pluck('id')->toArray();

            DB::beginTransaction();
            
            DesignElements::whereIn('id', $element_ids)->delete();
            DesignRender::whereIn('id', $render_ids)->delete();
            $design->delete();

            DB::commit();

            return $this->sendResponse([], 'Your Design has been deleted', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
