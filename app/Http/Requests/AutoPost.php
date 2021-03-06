<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoPost extends Base
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
            'social_id'     => 'required',
            'template_id'   => 'required',
            'post_type'     => 'required'
        ];
    }
}