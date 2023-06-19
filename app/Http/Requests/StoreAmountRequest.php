<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreAmountRequest extends FormRequest
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
            'customer_id' => 'required',
            'customer_store_password' => 'required|min:4|max:4',
            'store_id' => [ 'required', 
                            // check store exist with this vendor
                            Rule::exists('stores', 'id')
                                ->where('vendor_id', auth()->user()->id),
                                
                            // check subscription exist with this customer
                            Rule::exists('store_subscriptions', 'store_id')
                                ->where('customer_id', $this->customer_id)
                                ->where('customer_store_password', $this->customer_store_password)
                                ->where('deleted_at', null)
                        ],
            'amount' => 'required',
        ];
    }
}
