<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUnsubscriptionRequest extends FormRequest
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
            'store_id' => [ 'required', 'exists:stores,id', Rule::exists('store_subscriptions', 'store_id')->where('customer_id', auth()->user()->id)->where('deleted_at', null)],
            'customer_store_password' => 'required|min:4|max:4'
        ];
    }
}
