<?php

namespace App\Http\Requests\Api\V1\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'items'                          => 'required|array|min:1',
            'items.*.design_id'              => 'nullable|integer|exists:designs,id',
            'items.*.garment_variant_id'     => 'required|integer|exists:garment_variants,id',
            'items.*.veara_product_id'       => 'nullable|integer',
            'items.*.quantity'               => 'required|integer|min:1',
            'items.*.price'                  => 'required|numeric|min:0',
            'items.*.name'                   => 'required|string',
            'items.*.image'                  => 'nullable|string',
            'shipping.first_name'            => 'required|string',
            'shipping.last_name'             => 'required|string',
            'shipping.address1'              => 'required|string',
            'shipping.country'               => 'required|string',
            'shipping.region'                => 'nullable|string',
            'shipping.city'                  => 'required|string',
            'shipping.zip'                   => 'required|string',
        ];
    }
}
