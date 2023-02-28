<?php

namespace TaFarda\IAuth\app\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'admin_id' => ['required', 'exists:admins,id'],
            'title' => ['required', 'string', 'min:3', 'max:190', 'unique:products,title'],
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
            'sms_verify_template' => 'قالب تایید sms',
            'email_verify_template' => 'قالب تایید sms',
            'status' => 'وضعیت'
        ];
    }
}
