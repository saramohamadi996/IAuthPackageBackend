<?php

namespace TaFarda\IAuth\app\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'admin_id' => ['required', 'exists:admins,id'],
            'title' => ['required', 'string', 'min:3', 'max:190', Rule::unique('products')->ignore($this->product)],
            'description' => ['nullable', 'string', 'min:3', 'max:255'],
            'sms_verify_template' => ['required', 'string', 'min:3', 'max:50'],
            'email_verify_template' => ['required', 'string', 'min:3', 'max:50'],
            'status' => ['boolean']
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
            'admin' => 'شناسه ادمین',
            'title' => 'عنوان',
            'description' => 'توضیحات',
            'sms_verify_template' => 'شناسه پیامکی',
            'email_verify_template' => 'شناسه پست الکترونیکی',
            'status' => 'وضعیت'
        ];
    }
}
