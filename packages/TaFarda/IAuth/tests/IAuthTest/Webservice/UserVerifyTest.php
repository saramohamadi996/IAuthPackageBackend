<?php

namespace TaFarda\IAuth\Tests\IAuthTest\Webservice;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use TaFarda\IAuth\Models\Product;
use TaFarda\IAuth\Models\Admin;
use TaFarda\IAuth\Models\User;
use Tests\TestCase;
use Carbon\Carbon;

class UserVerifyTest extends TestCase
{
    use RefreshDatabase;

    protected Product $project;
    protected User $User;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'email' => 'project@tafarda.ir',
            'password' => Hash::make('123456789'),
            'webservice_call_token' => 'Sd34Fa5VkX',
            'status' => 1
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permission::all());

        Product::create([
            'title' => 'rsoon-test',
            'description' => 'rsoon-test',
            'sms_verify_template' => 'rsoon-verify',
            'email_verify_template' => 'rsoon-verify',
            'status' => 1,
            'admin_id' => $this->admin->id
        ]);
    }

    function test_invalid_verify_request()
    {
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'value' => '1111111111',
        ])->assertStatus(422);
    }

    function test_new_verify_request()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $newUser = User::where('mobile', $validMobile)->first();
        if (!$newUser)
            throw new \Exception();
        User::where('mobile', $validMobile)->delete();
    }

    function test_update_verify_request()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        $now = Carbon::now();
        User::where('mobile', $validMobile)->delete();
        User::create([
            'value' => $validMobile
        ]);
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);
        $existingUser = User::where('mobile', $validMobile)->first();
        if ($existingUser->otp_sent < $now)
            throw new \Exception();
        User::where('mobile', $validMobile)->delete();
    }

    function test_verify_request_after_OTP_expire_at()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();

        $response = $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile
        ])->assertStatus(201);
        $response->otp_sent = Carbon::now()->subMinutes(2);

        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'value' => $response
        ]);
        if ($response->otp_sent < Carbon::now())
            $response->assertStatus(201);
        User::where('mobile', $validMobile)->delete();
    }

    public function test_confirmation_of_create_request_with_wrong_token()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => '1111111111',
            'value' => $validMobile,
        ])->assertStatus(500);
    }

    function test_verify()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);
         $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '1111',
        ])->assertStatus(200);
    }

    function test_verify_after_OTP_expire_at()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $response = $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);
        $response->otp_sent = Carbon::now()->subMinutes(2);

        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '1111',
        ]);
        if ($response->otp_sent > Carbon::now())
            $response->assertStatus(404);
    }

    public function test_handle_mobile_and_OTP_mismatch()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '0000',
        ])->assertStatus(404);
    }

    public function test_display_validation_error_when_mobile_is_empty()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $this->postJson('api/v1/webservice/users/verify', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => '',
            'otp_code' => '1111',
        ])->assertStatus(404);
    }

    public function test_display_validation_error_when_OTP_is_empty()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $this->postJson('api/v1/webservice/users/verify', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '',
        ])->assertStatus(422);
    }
}

