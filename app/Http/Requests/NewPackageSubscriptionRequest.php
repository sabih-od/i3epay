<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewPackageSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'package_id' => [
                'required', 
                'exists:packages,id' // if package exits
            ],
            'store_id' => [
                'required', 
                Rule::exists('stores', 'id')->where('vendor_id', auth()->user()->id)
                // ->where('deleted_at', null)
            ], // if store exist with this vendor
        ];
    }
}
