<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBalanceRequest extends FormRequest
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

        $rules['store_id'] = ['required'];
        // dd(auth()->user()->id);

        if(auth()->user()->_role->name == 'customer')
        {
            $rules['store_id'][] =  // check subscription exist with this customer
                                    Rule::exists('store_subscriptions', 'store_id')
                                        ->where('customer_id', auth()->user()->id)
                                        ->where('is_accept', '1') // is accept customer request
                                        ->where('unsubscribe', '0') // and not have unsubscribe request
                                        ->where('deleted_at', null);
        }

        if(auth()->user()->_role->name == 'vendor')
        {
            $rules['store_id'][] = // check store exist with this vendor
                                    Rule::exists('stores', 'id')
                                        ->where('vendor_id', auth()->user()->id);
        }

        return $rules;
    }
}
