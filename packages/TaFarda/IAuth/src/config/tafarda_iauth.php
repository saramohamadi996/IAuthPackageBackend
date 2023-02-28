<?php
return [
    'pagination_size' => env('PAGINATION_SIZE', 20),
    'otp_code_min' => env('OTP_CODE_MIN', 1000),
    'otp_code_max' => env('OTP_CODE_MAX', 9999),
    'otp_sent_resend' => env('OTP_SENT_RESEND', 1),
    'otp_sent_update' => env('OTP_SENT_UPDATE', 1),
    'throttle_limit_per_minute' => env('THROTTLE_LIMIT_PER_MINUTE', 50),
    'api_key' => env(
        'API_KEY', '7158667246445A343841795A566C3974346A4631434B486F74526776576645396B46747233766935316A383D'
    ),
];
