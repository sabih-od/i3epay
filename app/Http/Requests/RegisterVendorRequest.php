<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
// use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterVendorRequest extends FormRequest
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
            'firstname' => ['required', 'max:255'],
            'lastname' => ['required', 'max:255'],
            'email' => ['required', 'unique:users', 'max:255'],
            'category' => ['required', 'max:255'],
            'address' => ['required', 'max:255'],
            'password' => ['required', 'max:8', 'min:8'],
            'package_id' => 'required|exists:packages,id',

            // store request details
            'store_name' => ['required'],
            'store_description' => ['required'],
            'store_address' => ['required'],
            'store_category' => ['required']
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(response()->json([
    //         'errors' => $validator->errors()
    //     ], 422));
    // }
}
