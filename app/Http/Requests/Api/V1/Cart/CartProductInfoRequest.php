<?php

namespace App\Http\Requests\Api\V1\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartProductInfoRequest extends FormRequest
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
            'printify_product_id' => 'required|string',
            'printify_variant_id' => 'required',
            // 'full_mockup' => 'nullable|string',
            'product_name' => 'required|string',
            'variant_title' => 'required',
            'variant_price' => 'required|numeric',

            'front_image'  => 'nullable|string',
            // 'back_image'  => 'nullable|string',

            'quantity' => 'required|integer',

            'design_id' => 'nullable|integer'
        ];
    }
}
