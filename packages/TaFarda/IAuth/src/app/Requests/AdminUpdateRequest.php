<?php

namespace TaFarda\IAuth\app\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class AdminUpdateRequest extends FormRequest
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
            'email' => ['nullable', 'string', 'min:3', 'max:50', 'email',
                Rule::unique('admins')->ignore($this->admin)],
            'password' => ['nullable', 'string', 'confirmed', Password::min(6)],
            'status' => ['boolean'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,name']
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
            'password' => 'پسورد',
            'status' => 'وضعیت',
            'permissions' => 'دسترسی'
        ];
    }
}
