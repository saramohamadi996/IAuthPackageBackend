<?php

namespace TaFarda\IAuth\Tests\IAuthTest\AdminAuth;

use Illuminate\Support\Facades\Hash;
use TaFarda\IAuth\Models\Admin;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Admin::create([
            'email' => 'johndoe@example.org',
            'password' => Hash::make('testpassword'),
            'webservice_call_token' => Str::random(12),
            'last_successful_login' => Carbon::now(),
            'status' => 1,
            'permissions' => ['products-index', 'users-index']
        ]);
    }

    public function test_return_user_and_access_token_after_successful_login()
    {
        $response = $this->withoutMiddleware()->postJson('api/v1/admins/login', [
            'email' => 'johndoe@example.org',
            'password' => 'testpassword',
        ]);
        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response['data']);
    }

    public function test_show_validation_error_when_both_fields_empty()
    {
        $this->withoutMiddleware()->postJson('api/v1/admins/login', [
            'email' => '',
            'password' => '',
        ])->assertStatus(422);
    }

    public function test_handle_email_password_mismatch()
    {
        $this->withoutMiddleware()->postJson('api/v1/admins/login', [
            'email' => 'johndoe@example.org',
            'password' => 'testp',
        ])->assertStatus(422);
    }

    public function test_non_authenticated_user_cannot_logout()
    {
        $this->withoutMiddleware()->postJson('api/v1/admins/login', [
            'email' => 'test@test.com',
            'password' => 'abcdabcd'
        ])->assertStatus(400);
    }

    public function test_non_authenticated_admin_cannot_logout()
    {
        $this->postJson('api/v1/admins/logout')->assertStatus(401);
    }

    public function test_authenticated_user_can_logout()
    {
        Sanctum::actingAs(Admin::first());
        $this->postJson('api/v1/admins/logout')->assertStatus(200);
    }
}
