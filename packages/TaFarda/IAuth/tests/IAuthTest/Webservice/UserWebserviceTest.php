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

class UserWebserviceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'email' => 'admin@tafarda.ir',
            'password' => Hash::make('123456789'),
            'webservice_call_token' => 'Sd34Fa5VkX',
            'status' => 1
        ]);
        $this->admin->assignRole('super-admin');
        $this->admin->givePermissionTo(Permission::all());
        $this->product = Product::create([
            'title' => 'rsoon-test',
            'description' => 'rsoon-test',
            'sms_verify_template' => 'rsoon-verify',
            'email_verify_template' => 'rsoon-verify',
            'status' => 1,
            'admin_id' => $this->admin->id
        ]);
    }

    function test_invalid_show_by_value()
    {
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/show-by-value', [
            'value' => '1111111111',
        ])->assertStatus(422);
    }

    public function testShowByValue()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $response = $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '1111',
        ]);
        $response->assertStatus(200);
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/show-by-value', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(200)
            ->assertJsonPath('profile.', null)
            ->assertStatus(200);
    }


    public function test_confirmation_of_show_by_value_with_wrong_token()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $response = $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '1111',
        ]);
        $response->assertStatus(200);
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/show-by-value', [
            'webservice_call_token' => '',
            'value' => $validMobile,
        ])->assertStatus(422);
    }

    public function test_handle_mobile_and_OTP_mismatch()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/update-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '0000',
        ])->assertStatus(403);
    }

    function test_update_request_after_OTP_expire_at()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $response = $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);
        $response->otp_sent = Carbon::now()->subMinutes(2);

        $this->withoutMiddleware()->postJson('api/v1/webservice/users/update-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_code' => '1111',
        ]);
        if ($response->otp_sent > Carbon::now())
            $response->assertStatus(404);
    }

    public function test_display_validation_error_when_mobile_is_empty()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();
        $this->withoutMiddleware()->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $this->postJson('api/v1/webservice/users/update-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => '',
            'otp_code' => '1111',
        ])->assertStatus(404);
    }

    function test_update_verify_request()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();

        $response = $this->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);
        $response->otp_sent = Carbon::now()->subHours(2);

        $this->postJson('api/v1/webservice/users/update-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(200)
        ->assertJsonFragment([
        'mobile' => $validMobile,
    ])->assertStatus(200);
        $existingUser = User::where('mobile', $validMobile)->first();
        if ($existingUser->otp_sent < Carbon::now())
            $response->assertOk();
        User::where('mobile', $validMobile)->delete();
    }

    function test_update_verify_request_subHour()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();

        $response = $this->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'otp_sent' => Carbon::now()->subMinute(),
        ])->assertStatus(201)
            ->assertJsonFragment([
                'mobile' => $validMobile,
            ])->assertStatus(201);
    }

    function test_invalid_update()
    {
        $this->withoutMiddleware()->putJson('api/v1/webservice/users/update', [
            'value' => '1111111111',
        ])->assertStatus(422);
    }

    public function test_update_a_user_information()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();

        $this->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $this->withoutMiddleware()->putJson('api/v1/webservice/users/update', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'first_name' => 'sara',
            'last_name' => 'moh',
        ])->assertStatus(200)
            ->assertJsonFragment([
                'mobile' => $validMobile,
                'first_name' => 'sara',
                'last_name' => 'moh',
            ])->assertStatus(200);
    }

    public function test_update_information_with_empty_value()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();

        $this->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $this->withoutMiddleware()->putJson('api/v1/webservice/users/update', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
            'first_name' => '',
            'last_name' => '',
        ])->assertStatus(200)
            ->assertJsonFragment([
                'mobile' => $validMobile,
                'first_name' => '',
                'last_name' => '',
            ])->assertStatus(200);
    }

    public function test_confirmation_of_update_a_user_information_with_wrong_token()
    {
        $validMobile = '9' . rand(100000000, 999999999);
        User::where('mobile', $validMobile)->delete();

        $this->postJson('api/v1/webservice/users/verify-request', [
            'webservice_call_token' => $this->admin->webservice_call_token,
            'value' => $validMobile,
        ])->assertStatus(201);

        $this->withoutMiddleware()->putJson('api/v1/webservice/users/update', [
            'webservice_call_token' => '',
            'value' => $validMobile,
            'first_name' => 'sara',
            'last_name' => 'moh',
        ])->assertStatus(422);
    }
}


