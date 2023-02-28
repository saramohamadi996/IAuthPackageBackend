<?php

namespace TaFarda\IAuth\app\Requests;

use Illuminate\Foundation\Http\FormRequest;
use TaFarda\IAuth\Rules\ValidMobile;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'mobile' => ['nullable', 'required_without:email', 'string',
                new ValidMobile(), 'size:10','unique:users,mobile'],
            'email' => ['nullable', 'string', 'min:3', 'max:50', 'email',
                'unique:users,email' . request()->route('users')],
            'status' => ['boolean'],
            'product_ids' => ['array', 'nullable'],
            'product_ids.*' => ['required', 'exists:products,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'email' => 'ایمیل',
            'mobile' => 'موبایل',
            'status' => 'وضعیت',
            'product_ids' => 'محصول',
        ];
    }

}
