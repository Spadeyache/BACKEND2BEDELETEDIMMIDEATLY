<?php

namespace App\Http\Requests\Api\V1\Design;

use Illuminate\Foundation\Http\FormRequest;

class DesignSaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            // 'printify_variant_id' => 'required',
            // 'product_name' => 'required|string',
            // 'variant_title' => 'required',
            // 'variant_price' => 'required|numeric',
            // 'printify_product_id'   => 'nullable',
            'veara_product_id'      => 'nullable',
            'full_mockup'           => 'required|string',

            'front_image'           => 'nullable|string',
            'back_image'            => 'nullable|string',
            // 'right_sleeve_image'    => 'nullable|string',
            // 'left_sleeve_image'     => 'nullable|string',
            // 'neck_image'            => 'nullable|string',
            'print_files'           => 'nullable|array',
            'print_files.*'         => 'nullable|string',
            'front_elements'        => 'nullable|array',
            'back_elements'         => 'nullable|array',
        ];
    }
}
