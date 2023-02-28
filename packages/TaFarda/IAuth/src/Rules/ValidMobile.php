<?php

namespace TaFarda\IAuth\Rules;

use Illuminate\Contracts\Validation\Rule;
class ValidMobile implements Rule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes( $attribute, mixed $value): bool
    {
        return preg_match('/^(\+98|0098|98|0)?9\d{9}$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('فرمت موبایل نامعتبر است. شماره موبایل باید با 9 شروع بشود و بدون فاصله وارد شود.');
    }
}
