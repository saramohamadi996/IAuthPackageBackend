<?php

namespace TaFarda\IAuth\Tests\IAuthTest\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use TaFarda\IAuth\Models\Admin;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = Admin::create([
            'email' => 'admin@tafarda.ir',
            'password' => Hash::make('123456789'),
            'webservice_call_token' => Str::random(12),
            'status' => 1
        ]);
        $this->superAdmin->assignRole('super-admin');
        $this->superAdmin->givePermissionTo(Permission::all());

        $this->admin = Admin::create([
            'email' => 'hasan@tafarda.ir',
            'password' => Hash::make('Admin$12test'),
            'webservice_call_token' => Str::random(12),
            'status' => 1
        ]);
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo(Permission::all());

        $this->adminTest = Admin::create([
            'email' => 'akbar@gmail.com',
            'password' => Hash::make('password'),
            'webservice_call_token' => Str::random(12),
            'status' => 1,
            'permissions' => ['products-index', 'admins-index']
        ]);
    }

    public function test_index()
    {
        Sanctum::actingAs(Admin::first());
        $this->getJson('api/v1/admins')
            ->assertStatus(200)
            ->assertJsonFragment([
                'email' => 'admin@tafarda.ir',
            ])->assertJsonFragment([
                'email' => 'hasan@tafarda.ir',
            ])->assertJsonFragment([
                'email' => 'akbar@gmail.com',
            ])
            ->assertStatus(200);
    }

    public function test_store_a_product()
    {
        $this->actingAs($this->admin);
        $this->postJson('/api/v1/products', [
            'admin_id' => $this->admin->id,
            'title' => 'test',
            'description' => 'pro description',
            'sms_verify_template' => 'sms verify',
            'email_verify_template' => 'email verify',
            'status' => 0,
        ])->assertStatus(201);
    }

    public function test_getting_the_list_of_available_admins()
    {
        Sanctum::actingAs(Admin::first());
        $this->getJson('api/v1/admins')->assertStatus(200)
            ->assertJsonFragment([
                'email' => 'admin@tafarda.ir',
            ])->assertJsonFragment([
                'email' => 'akbar@gmail.com',
            ])->assertStatus(200);
    }

    public function test_authenticated_admin_can_get_admin_profiles()
    {
        $this->actingAs($this->admin);
        $this->get('api/v1/admins/profile')->assertStatus(200);
    }

    public function test_non_authenticated_admin_cannot_get_admin_profiles()
    {
        $this->get('api/v1/admins/profile')->assertStatus(500);
    }

    public function test_store_a_admin()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/admins', [
            'email' => 'johnd@example.org',
            'password' => '123456789',
            'password_confirmation' => '123456789',
            'status' => 1,
            'permissions' => ['admins-index', 'admins-store', 'admins-update']
        ])->assertStatus(201);
    }

    public function test_handle_iterative_title()
    {
        $this->actingAs($this->admin);
        $this->post('api/v1/admins', [
            'email' => 'johndoe2@example.org',
            'password' => '123456789',
            'password_confirmation' => '123456789',
            'status' => 1,
            'permissions' => ['admins-index', 'admins-store', 'admins-update']
        ]);
        $this->post('api/v1/admins', [
            'email' => 'johndoe2@example.org',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 0,
            'permissions' => ['product-index', 'user-index']
        ])->assertSessionHasErrors();
    }

    public function test_validation_error_display_when_permissions_is_empty()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/admins', [
            'email' => 'johndoe12@example.org',
            'password' => '123456789',
            'password_confirmation' => '123456789',
            'status' => 1,
            'permissions' => ''
        ])->assertStatus(422);
    }

    public function test_create_admin_with_empty_fields()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/admins', [
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'status' => 1,
            'permissions' => []
        ])->assertStatus(422);
    }

    public function test_the_non_authenticated_admin_cannot_create_an_admin()
    {
        $this->postJson('api/v1/admins', [
            'email' => 'johndoe121@example.org',
            'password' => '123456789',
            'password_confirmation' => '123456789',
            'status' => 1,
            'permissions' => ['admins-index', 'admins-store']
        ])->assertStatus(401);
    }

    public function test_handle_unacceptable_password()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/admins', [
            'email' => 'test@gmail.com',
            'password' => '1',
            'password_confirmation' => '1',
            'status' => 0,
            'permissions' => ['admins-index']
        ])->assertStatus(422);
    }

    public function test_display_validation_error_when_password_field_is_empty()
    {
        $this->actingAs($this->admin);
        $this->postJson('api/v1/admins', [
            'email' => 'test@gmail.com',
            'password' => '',
            'password_confirmation' => '',
            'status' => 0,
            'permissions' => ['admins-index']
        ])->assertStatus(422);
    }

    public function test_handle_password_confirmation_mismatch()
    {
        $this->actingAs($this->admin);
        $this->post('api/v1/admins', [
            'email' => 'test@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'secret',
            'status' => 0,
            'permissions' => ['admins-index']
        ])->assertStatus(500);
    }

    public function test_admin_password_update_without_email()
    {
        $this->actingAs($this->admin);
        $this->putJson('/api/v1/admins/' . $this->admin->id, [
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 1,
            'permissions' => ['admins-index']
        ])->assertStatus(200);
    }

    public function test_the_non_authenticated_admin_cannot_update_an_admin()
    {
        $this->putJson('/api/v1/admins/' . $this->adminTest->id, [
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 1,
            'permissions' => ['admins-index', 'admins-store', 'admins-update']
        ])->assertStatus(401);
    }

    public function test_handle_unacceptable_update_password()
    {
        $this->actingAs($this->admin);
        $this->putJson('api/v1/admins/' . $this->admin->id, [
            'email' => 'johndoe3@gmail.com',
            'password' => '1',
            'password_confirmation' => '1',
            'status' => 0,
            'permissions' => ['admins-update']
        ])->assertStatus(422);
    }

    public function test_show_validation_error_when_both_fields_empty()
    {
        $this->actingAs($this->admin);
        $this->putJson('api/v1/admins', [
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'status' => 0,
            'permissions' => ['admins-index', 'admins-store', 'admins-update']
        ])->assertStatus(405);
    }
}
