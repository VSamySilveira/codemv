<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private const myRoutes = [
        'index' => 'api.genres.index',
        'show' => 'api.genres.show',
        'store' => 'api.genres.store',
        'update' => 'api.genres.update',
        'destroy' => 'api.genres.destroy'
    ];

    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
    }

    /////////// TESTING FUNCTIONS ///////////
    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['genre' => $this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }
    
    public function testStore()
    {
        $data = ['name' => 'Test1'];
        $response = $this->assertStore(
            $data, 
            $data + ['is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'Test1',
            'is_active' => false
        ];
        $this->assertStore(
            $data, 
            $data + ['is_active' => false]
        );
    }

    public function testUpdate()
    {
        $this->genre = factory(Genre::class)->create([
            'is_active' => false
        ]);

        $data = [
            'name' => 'Test1',
            'is_active' => true
        ];
        $response = $this->assertUpdate(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::myRoutes['destroy'], ['genre' => $this->genre->id]));
        $response->assertStatus(204);
        //Check if deleted
        $genre = Genre::find($this->genre->id);
        $this->assertNull($genre);
        //Check if trashed
        $genre = Genre::onlyTrashed()->find($this->genre->id);
        $this->assertNotNull($genre);
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function routeStore()
    {
        return route(self::myRoutes['index']);
    }

    protected function routeUpdate()
    {
        return route(self::myRoutes['update'], ['genre' => $this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }
}
