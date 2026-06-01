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
            'veara_product_id' => ['required', 'integer'],
            'garment_variant_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            // 'positioning' => ['required', 'array'],
            // 'positioning.front' => ['nullable', 'array'],
            // 'positioning.front.x' => ['required_with:positioning.front', 'numeric'],
            // 'positioning.front.y' => ['required_with:positioning.front', 'numeric'],
            // 'positioning.front.scale' => ['required_with:positioning.front', 'numeric'],
            // 'positioning.front.angle' => ['required_with:positioning.front', 'numeric'],
            // 'positioning.back' => ['nullable', 'array'],
            // 'positioning.back.x' => ['required_with:positioning.back', 'numeric'],
            // 'positioning.back.y' => ['required_with:positioning.back', 'numeric'],
            // 'positioning.back.scale' => ['required_with:positioning.back', 'numeric'],
            // 'positioning.back.angle' => ['required_with:positioning.back', 'numeric'],
        ];
    }
}
