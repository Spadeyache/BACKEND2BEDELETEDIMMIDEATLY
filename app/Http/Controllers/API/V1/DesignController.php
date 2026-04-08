<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Design\DesignSaveRequest;
use App\Http\Resources\Api\V1\Design\DesignGetResource;
use App\Http\Resources\Api\V1\Design\DesignRenderGetResource;
use App\Http\Resources\Api\V1\DesignPageGetResource;
use App\Models\Design;
use App\Models\DesignRender;
use App\Traits\ApiResponse;
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

            $designs = Design::with('designImages')->get();

            return $this->sendResponse(DesignPageGetResource::collection($designs),'Product design saved successfully',200);
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
            $design = Design::create([
                'user_id'               => $user_id,
                'veara_product_id'      => $data['veara_product_id'] ?? null,
                'printify_product_id'   => $data['printify_product_id'] ?? null,
                'mockup_image'          => $data['full_mockup'],
                'print_files'           => $data['print_files'] ?? null,
                'created_by'            => $user_id
            ]);
            
            $imageFields = ['front_image', 'back_image', 'right_sleeve_image', 'left_sleeve_image', 'neck_image'];

            $designRenders = [];

            foreach ($imageFields as $field) {
                if ($request->filled($field)) {
                    $designRenders[$field] = DesignRender::create([
                        'design_id'  => $design->id,
                        'area_name'  => str_replace('_image', '', $field), // "front_image" -> "front"
                        'image_url'  => $data[$field],
                        'created_by' => $user_id,
                    ]);
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
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }
}
