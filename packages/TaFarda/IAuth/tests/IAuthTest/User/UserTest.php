<?php

namespace TaFarda\IAuth\Tests\IAuthTest\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use TaFarda\IAuth\Models\Admin;
use TaFarda\IAuth\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected user $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create([
            'email' => 'admin@tafarda.ir',
            'password' => Hash::make('123456789'),
            'webservice_call_token' => Str::random(12),
            'status' => 1
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permission::all());

        $this->user = User::create([
            'email' => 'sara@example.org',
            'mobile' => '9905749544',
            'status' => 1
        ]);
        $this->user->profile()->create([
            'first_name' => 'john',
            'last_name' => 'doe'
        ]);
    }

    public function test_index()
    {
        Sanctum::actingAs(Admin::first());
        $this->getJson('api/v1/users')->assertStatus(200)
            ->assertJsonFragment([
                'email' => 'sara@example.org',
                'mobile' => '9905749544',
                'status' => 1
            ])->assertStatus(200);
    }

    public function test_store_a_user()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/users', [
            'email' => 'johndoe1@example.org',
            'mobile' => '9054591213',
            'status' => 1
        ])->assertStatus(201);
    }

    public function test_create_user_with_mobile()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/users', [
            'email' => '',
            'mobile' => '9054591213',
            'status' => 1
        ])->assertStatus(201);
    }

    public function test_create_user_with_email()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/users', [
            'email' => 'test@gmail.com',
            'mobile' => '',
            'status' => 0
        ])->assertStatus(201);
    }

    public function test_show_validation_error_when_both_fields_empty()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/users', [
            'email' => '',
            'mobile' => '',
            'status' => 1
        ])->assertStatus(422);
    }

    public function test_display_validation_error_when_email_is_invalid()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/users', [
            'email' => 'test',
            'mobile' => '',
            'status' => 1
        ])->assertStatus(422);
    }

    public function test_validation_error_display_when_mobile_is_invalid()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/users', [
            'email' => 'test@gmail.com',
            'mobile' => 9054591212,
            'status' => 1
        ])->assertStatus(422);
    }

    public function test_the_non_authenticated_admin_cannot_create_an_user()
    {
        $this->postJson('api/v1/users', [
            'email' => 'johndoe1@example.org',
            'mobile' => '9054591213',
            'status' => 1
        ])->assertStatus(401);
    }

    public function test_handle_iterative_email()
    {
        $this->actingAs($this->admin);
        $this->post('api/v1/users', [
            'email' => 'johndoe2@example.org',
            'mobile' => '',
            'status' => 1
        ]);
        $this->post('api/v1/users', [
            'email' => 'johndoe2@example.org',
            'mobile' => '',
            'status' => 1
        ])->assertSessionHasErrors();
    }

    public function test_handle_iterative_mobile()
    {
        $this->actingAs($this->admin);
        $this->post('api/v1/users', [
            'email' => 'test2@example.org',
            'mobile' => '9054591211',
            'status' => 1
        ]);
        $this->post('api/v1/users', [
            'email' => 'johndoe2@example.org',
            'mobile' => '9054591211',
            'status' => 1
        ])->assertSessionHasErrors();
    }

    public function test_update_a_user()
    {
        $this->actingAs($this->admin);
        $this->putJson('api/v1/users/' . $this->user->id, [
            'email' => 'sara@example.org',
            'mobile' => '9905749544',
            'status' => 0
        ])->assertStatus(200);
    }

    public function test_the_non_authenticated_admin_cannot_update_an_user()
    {
        $this->putJson('api/v1/users/' . $this->user->id, [
            'email' => 'sara@example.org',
            'mobile' => '9905749544',
            'status' => 0
        ])->assertStatus(401);
    }

    public function test_validation_error_display_when_data_is_invalid()
    {
        $this->actingAs($this->admin);
        $this->putJson('api/v1/users/' . $this->user->id, [
            'email' => 'sara@example.org',
            'mobile' => 9054591212,
            'status' => 1
        ])->assertStatus(422);
    }

    public function testUpdateUserProfileInformation()
    {
        $this->actingAs($this->admin);
        $this->putJson('api/v1/users/' . $this->user->id, [
            'email' => '',
            'mobile' => '',
            'status' => 0
        ])->assertStatus(200);
    }
}
