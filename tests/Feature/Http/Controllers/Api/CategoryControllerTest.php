<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private const myRoutes = [
        'index' => 'api.categories.index',
        'show' => 'api.categories.show',
        'store' => 'api.categories.store',
        'update' => 'api.categories.update',
        'destroy' => 'api.categories.destroy'
    ];

    private $category;
    private $serializedFields = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create(['is_active' => false]);
    }

    ////////// TESTING FUNCTIONS ///////////
    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);

        $resource = CategoryResource::collection(collect([$this->category]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['category' => $this->category->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
        $id = $response->json('data_id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);
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
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $data = [
            'name' => 'Test1',
            'description' => 'Olá',
            'is_active' => false
        ];
        $this->assertStore(
            $data, 
            $data + ['description' => 'Olá', 'is_active' => false]
        );
        
        $id = $response->json('data_id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);
    }

    public function testUpdate()
    {
        //Test if update the fields name, description and is_active
        $data = [
            'name' => 'Test1',
            'description' => 'Olá',
            'is_active' => true
        ];
        $response = $this->assertUpdate(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        
        $id = $response->json('data_id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);

        //Test if update field description to null
        $data = [
            'name' => 'Test1',
            'description' => ''
        ];
        $response = $this->assertUpdate(
            $data,
            array_merge($data, ['description' => null])
        );
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::myRoutes['destroy'], ['category' => $this->category->id]));
        $response->assertStatus(204);
        $uuid = $this->category->id;
        //Check if deleted
        $category = Category::find($uuid);
        $this->assertNull($category);
        //Check if trashed
        $category = Category::onlyTrashed()->find($uuid);
        $this->assertNotNull($category);
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function routeStore()
    {
        return route(self::myRoutes['index']);
    }

    protected function routeUpdate()
    {
        return route(self::myRoutes['update'], ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
