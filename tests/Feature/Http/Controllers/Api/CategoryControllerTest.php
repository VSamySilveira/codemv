<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    protected function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('api.categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('api.categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        /////////// CREATE TESTS ///////////
        //Test Name Required Validation
        $response = $this->json('POST', route('api.categories.store'), []);
        $this->assertInvalidationRequired($response);

        //Test Name Max Validation
        $response = $this->json('POST', route('api.categories.store'), [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
        
        /////////// UPDATE TESTS ///////////
        //Test Name Required Validation
        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route('api.categories.update', ['category' => $category->id]), []);
        $this->assertInvalidationRequired($response);
        
        
        //Test Name Max Validation
        $response = $this->json('PUT', route('api.categories.update', ['category' => $category->id]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    
    public function testStore()
    {
        //First description and is_active ommited
        $response = $this->json('POST', route('api.categories.store'), [
            'name' => 'Test1'
        ]);
        $id = $response->json('id');
        $category = Category::find($id);
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertNull($response->json('description'));
        $this->assertTrue($response->json('is_active'));

        //Second description and is_active defined
        $response = $this->json('POST', route('api.categories.store'), [
            'name' => 'Test1',
            'description' => 'Olá',
            'is_active' => false
        ]);
        $id = $response->json('id');
        $category = Category::find($id);
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertEquals('Olá', $response->json('description'));
        $this->assertFalse($response->json('is_active'));
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
                'is_active' => false
            ]);

        $response = $this->json('PUT', route('api.categories.update', ['category' => $category->id]), [
            'name' => 'Test1',
            'description' => 'Olá Mundo',
            'is_active' => true
        ]);
        $id = $response->json('id');
        $category = Category::find($id);
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'name' => 'Test1',
                'description' => 'Olá Mundo',
                'is_active' => true
            ]);
        
        $response = $this->json('PUT', route('api.categories.update', ['category' => $category->id]), [
            'name' => 'Test1',
            'description' => '',
            'is_active' => true
        ]);
        $response->assertJsonFragment(['description' => null]);

        
    }

}
