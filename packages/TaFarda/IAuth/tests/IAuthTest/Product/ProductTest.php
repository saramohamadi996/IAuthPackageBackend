<?php

namespace TaFarda\IAuth\Tests\IAuthTest\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use TaFarda\IAuth\Models\Product;
use TaFarda\IAuth\Models\Admin;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $productAdmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->productAdmin = Admin::create([
            'email' => 'productAdmin@tafarda.ir',
            'password' => Hash::make('123456789'),
            'webservice_call_token' => Str::random(12),
            'status' => 1
        ]);
        $this->productAdmin->assignRole('admin');
        $this->productAdmin->givePermissionTo(Permission::all());

        $this->admin2 = Admin::create([
            'email' => 'ali@gmail.com',
            'password' => Hash::make('password'),
            'webservice_call_token' => Str::random(12),
            'status' => 1,
            'permissions' => ['products-index', 'users-index']
        ]);
        $this->product = Product::create([
            'admin_id' => $this->productAdmin->id,
            'title' => 'This is a test product.',
            'description' => 'Test product description.',
            'sms_verify_template' => 'product-sms-verify',
            'email_verify_template' => 'product-email-verify',
            'status' => 1
        ]);
    }

    public function test_index()
    {
        Sanctum::actingAs(Admin::first());
        $this->getJson('/api/v1/products')->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'This is a test product.',
                'description' => 'Test product description.',
                'sms_verify_template' => 'product-sms-verify',
                'email_verify_template' => 'product-email-verify',
                'status' => 1
            ])->assertStatus(200);
    }

    public function test_store_a_product()
    {
        $this->actingAs($this->productAdmin);
        $this->postJson('/api/v1/products', [
            'admin_id' => $this->productAdmin->id,
            'title' => 'product',
            'description' => 'pro description',
            'sms_verify_template' => '123',
            'email_verify_template' => '123',
            'status' => 1,
        ])->assertStatus(201);
    }

    public function test_handle_iterative_title()
    {
        $this->actingAs($this->productAdmin);
        $this->postJson('/api/v1/products', [
            'admin_id' => $this->productAdmin->id,
            'title' => 'product2',
            'description' => 'pro',
            'sms_verify_template' => 'sms verify',
            'email_verify_template' => 'email verify',
            'status' => 1,
        ])->assertStatus(201);

        $this->postJson('/api/v1/products', [
            'admin_id' => $this->productAdmin->id,
            'title' => 'product2',
            'description' => ' description',
            'sms_verify_template' => 'template',
            'email_verify_template' => 'template',
            'status' => 0,
        ])->assertStatus(422);
    }


    public function test_display_the_validation_error_of_product_creation_with_invalid_admin()
    {
        $this->actingAs($this->productAdmin);
        $this->postJson('/api/v1/products', [
            'admin_id' => 123,
            'title' => 'product',
            'description' => 'pro description',
            'sms_verify_template' => '123',
            'email_verify_template' => '123',
            'status' => 1,
        ])->assertStatus(422);
    }

    public function test_the_non_authenticated_admin_cannot_create_an_product()
    {
        $this->postJson('/api/v1/products', [
            'admin_id' => $this->productAdmin->id,
            'title' => 'product',
            'description' => 'pro description',
            'sms_verify_template' => '123',
            'email_verify_template' => '123',
            'status' => 1,
        ])->assertStatus(401);
    }

    public function test_update_a_product()
    {
        $this->actingAs($this->productAdmin);
        $this->putJson('/api/v1/products/' . $this->product->id, [
            'admin_id' => $this->productAdmin->id,
            'title' => 'product',
            'description' => 'product description',
            'sms_verify_template' => 'kaveh negar',
            'email_verify_template' => 'kaveh negar',
            'status' => 1
        ])->assertStatus(200);
    }
    public function test_display_validation_error_when_the_data_is_invalid()
    {
        $this->actingAs($this->productAdmin);
        $this->post('/api/v1/products', [
            'admin_id' => $this->productAdmin->id,
            'title' => '',
            'description' => '',
            'sms_verify_template' => 'ka',
            'email_verify_template' => 'k',
            'status' => 1
        ])->assertSessionHasErrors();
    }

    public function test_display_the_validation_error_of_product_update_with_invalid_admin()
    {
        $this->actingAs($this->productAdmin);
        $this->putJson('/api/v1/products/' . $this->product->id, [
            'admin_id' => 99,
            'title' => '',
            'description' => '',
            'sms_verify_template' => 'ka',
            'email_verify_template' => 'k',
            'status' => 1
        ])->assertStatus(422);
    }

    public function test_the_non_authenticated_admin_cannot_update_an_product()
    {
        $this->putJson('/api/v1/products/' . $this->product->id, [
            'admin_id' => $this->productAdmin->id,
            'title' => 'product',
            'description' => 'product description',
            'sms_verify_template' => 'kaveh negar',
            'email_verify_template' => 'kaveh negar',
            'status' => 1
        ])->assertStatus(401);
    }

    public function test_invalid_update()
    {
        $this->actingAs($this->productAdmin);
        $this->putJson('/api/v1/products/' . $this->product->id, [
            'admin_id' => $this->productAdmin->id,
            'title' => 'po',
            'description' => 0,
            'sms_verify_template' => '3',
            'email_verify_template' => '3',
            'status' => 1
        ])->assertStatus(422);
    }
}
