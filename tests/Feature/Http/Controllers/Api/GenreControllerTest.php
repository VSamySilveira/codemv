<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    /////////// AUXILIARY FUNCTIONS ///////////
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

    /////////// TEST FUNCTIONS ///////////
    public function testIndex()
    {
        $category = factory(Genre::class)->create();
        $response = $this->get(route('api.genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Genre::class)->create();
        $response = $this->get(route('api.genres.show', ['genre' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        /////////// CREATE TESTS ///////////
        //Test Name Required Validation
        $response = $this->json('POST', route('api.genres.store'), []);
        $this->assertInvalidationRequired($response);

        //Test Name Max Validation
        $response = $this->json('POST', route('api.genres.store'), [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
        
        /////////// UPDATE TESTS ///////////
        //Test Name Required Validation
        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT', route('api.genres.update', ['genre' => $genre->id]), []);
        $this->assertInvalidationRequired($response);
        
        
        //Test Name Max Validation
        $response = $this->json('PUT', route('api.genres.update', ['genre' => $genre->id]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        /////////// DELETE TESTS ///////////
        $genre = factory(Genre::class)->make(); //Obs: Not created: ->create()
        $response = $this->json('DELETE', route('api.genres.destroy', ['genre' => $genre->id]));
        $response
            ->assertStatus(405)
            ->assertSeeText("methodNotAllowed");
    }

    
    public function testStore()
    {
        //First is_active omitted
        $response = $this->json('POST', route('api.genres.store'), [
            'name' => 'Test1'
        ]);
        $id = $response->json('id');
        $genre = Genre::find($id);
        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        //Second is_active defined
        $response = $this->json('POST', route('api.genres.store'), [
            'name' => 'Test1',
            'is_active' => false
        ]);
        $id = $response->json('id');
        $genre = Genre::find($id);
        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertFalse($response->json('is_active'));
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
                'is_active' => false
            ]);

        $response = $this->json('PUT', route('api.genres.update', ['genre' => $genre->id]), [
            'name' => 'Test1',
            'is_active' => true
        ]);
        $id = $response->json('id');
        $genre = Genre::find($id);
        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'Test1',
                'is_active' => true
            ]);
    }

    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json('DELETE', route('api.genres.destroy', ['genre' => $genre->id]));
        $response->assertStatus(204);
        $uuid = $genre->id;
        //Check if deleted
        $genre = Genre::find($uuid);
        $this->assertNull($genre);
        //Check if trashed
        $genre = Genre::onlyTrashed()->find($uuid);
        $this->assertNotNull($genre);
    }

}
