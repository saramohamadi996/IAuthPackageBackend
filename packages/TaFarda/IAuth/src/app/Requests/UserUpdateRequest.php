<?php

namespace TaFarda\IAuth\app\Requests;

use Illuminate\Foundation\Http\FormRequest;
use TaFarda\IAuth\Rules\ValidMobile;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
                Rule::unique('users')->ignore($this->user)],
            'mobile' => ['nullable', 'string', 'size:10', new ValidMobile(),
                Rule::unique('users')->ignore($this->user)],
            'status' => ['boolean'],
            'product_ids' => ['array', 'nullable'],
            'product_ids.*' => ['required', 'exists:products,id'],
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'father_name' => ['nullable', 'string'],
            'identity_number' => ['nullable', 'string'],
            'national_code' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'string'],
            'sex' => ['boolean'],
            'state' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
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
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'father_name' => 'نام پدر',
            'identity_number' => 'شماره شناسنامه',
            'national_code' => 'کد ملی',
            'birth_date' => 'تاریخ تولد',
            'sex' => 'جنسیت',
            'state' => 'استان',
            'city' => 'شهر',
            'address' => 'آدرس',
            'phone' => 'تلفن',
            'postal_code' => 'کد پستی',
        ];
    }

}
