<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeductAmountRequest extends FormRequest
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
                                ->where('customer_id', $this->customer_id) // verify customer id
                                ->where('customer_store_password', $this->customer_store_password) // verify store password
                                ->where('is_accept', '1') // is accept customer request
                                ->where('unsubscribe', '0') // and not have unsubscribe request
                                ->where('deleted_at', null)
                        ],
            'amount' => 'required',
        ];
    }
}
