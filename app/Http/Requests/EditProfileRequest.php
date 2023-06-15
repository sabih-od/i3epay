<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditProfileRequest extends FormRequest
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
        $rules = [];

        $rules['firstname'] = ['required', 'max:255'];
        $rules['lastname'] = ['required', 'max:255'];
        $rules['address'] = ['required', 'max:255'];

        if(auth()->user()->_role->name == 'vendor')
        {
            // store request details
            $rules['store_id'] = [ 
                'required',
                Rule::exists('stores', 'id')->where('vendor_id', auth()->user()->id)
            ];
            $rules['store_name'] = ['required'];
            $rules['store_description'] = ['required'];
            $rules['store_address'] = ['required'];
            $rules['store_category'] = ['required'];
        }
        elseif(auth()->user()->_role->name == 'customer')
        {
            $rules['phone'] = ['required', 'integer'];
        }

        return $rules;
    }
}