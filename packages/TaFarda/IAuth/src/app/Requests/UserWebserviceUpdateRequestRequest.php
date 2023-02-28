<?php

namespace TaFarda\IAuth\app\Requests;

use Illuminate\Foundation\Http\FormRequest;
use TaFarda\IAuth\Models\Admin;

class UserWebserviceUpdateRequestRequest extends FormRequest
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
        if (preg_match('/^(\+98|0098|98|0)?9\d{9}$/', $this->value))
            $rules = ['nullable', 'size:10', 'regex:/^(\+98|0098|98|0)?9\d{9}$/'];
        else
            $rules = ['nullable', 'string', 'email'];

        return [
            'value' => $rules,
            'otp_code' => ['nullable', 'string', 'min:4', 'max:4']
        ];
    }

    /**
     * merge the type for the user verify request.
     *
     * @param $key
     * @param $default
     * @return array
     */
    public function validated($key = null, $default = null): array
    {
        if (preg_match('/^(\+98|0098|98|0)?9\d{9}$/', $this->value))
            $type = 'mobile';
        else
            $type = 'email';

        $admin = Admin::where('webservice_call_token', $this->webservice_call_token)->first();

        return array_merge(
            parent::validated(), [
                'type' => $type,
                'admin' => $admin,
            ]
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return string[]
     */
    public function attributes(): array
    {
        return [
            'value' => 'ایمیل یا موبایل',
            'otp_code' => 'کد فعالسازی'
        ];
    }

}
