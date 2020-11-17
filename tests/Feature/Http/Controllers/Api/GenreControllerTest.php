<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private const myRoutes = [
        'index' => 'api.genres.index',
        'show' => 'api.genres.show',
        'store' => 'api.genres.store',
        'update' => 'api.genres.update',
        'destroy' => 'api.genres.destroy'
    ];

    private $genre;
    private $sendData;
    private $serializedFields = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();

        $this->sendData = [
            'name' => 'Test1',
            'is_active' => true
        ];
    }

    /////////// TESTING FUNCTIONS ///////////
    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => $this->serializedFields
                    ],
                    'meta' => [],
                    'links' => []
                ]
            );
        $this->assertResource($response, GenreResource::collection(collect([$this->genre])));
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['genre' => $this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                    'data' => $this->serializedFields
            ])
            ->assertJsonFragment($this->genre->toArray());
        
        $this->assertResource($response, new GenreResource($this->genre));
    }

    public function testInvalidationRequired()
    {
        $data = [
            'name' => '',
            'categories_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationMin()
    {
        $category = factory(Category::class)->create();
        $data = ['name' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'min.string', ['min' => 2]);
        $this->assertInvalidationInUpdateAction($data, 'min.string', ['min' => 2]);
    }

    public function testInvalidationCategoriesIdField()
    {
        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            [
                'data' => ['categories_id' => 'x'],
                'rule' => 'array'
            ],
            [
                'data' => ['categories_id' => [50]],
                'rule' => 'exists'
            ],
            [
                'data' => ['categories_id' => [$category->id]],
                'rule' => 'exists'
            ]
        ];
        $this->assertInvalidationSaveRelationshipFields($data);
    }

    private function assertInvalidationSaveRelationshipFields($data)
    {
        foreach ($data as $row)
        {
            $this->assertInvalidationInStoreAction($row['data'], $row['rule']);
            $this->assertInvalidationInUpdateAction($row['data'], $row['rule']);
        }
    }

    public function testSave()
    {
        $categoryId = factory(Category::class)->create()->id; 

        $data = [
            [
                'send_data' => [
                    'name' => 'test',
                    'categories_id' => [$categoryId]
                ],
                'test_data' => [
                    'name' => 'test',
                    'is_active' => true
                ]
            ],
            [
                'send_data' => [
                    'name' => 'test',
                    'is_active' => false,
                    'categories_id' => [$categoryId]
                ],
                'test_data' => [
                    'name' => 'test',
                    'is_active' => false
                ]
            ]
        ];

        foreach ($data as $test)
        {
            $response = $this->assertStore(
                $test['send_data'],
                $test['test_data']
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource($response, new GenreResource(Genre::find($response->json('data.id'))));

            $response = $this->assertUpdate(
                $test['send_data'],
                $test['test_data']
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource($response, new GenreResource(Genre::find($response->json('data.id'))));
        }
    }

    /*
    protected function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $genreId,
            'category_id' => $categoryId
        ]);
    }
    */

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();

        $sendData = [
            'name' => 'Test1',
            'categories_id' => [$categoriesId[0]]
        ];

        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('data.id')
        ]);

        $sendData = [
            'name' => 'Test1',
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ];
        $response = $this->json(
            'PUT', 
            route(self::myRoutes['update'], ['genre' => $response->json('data.id')]),
            $sendData
        );
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[1],
            'genre_id' => $response->json('data.id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[2],
            'genre_id' => $response->json('data.id')
        ]);
    }


    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);
        
        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);
        
        $hasError = false;
        try 
        {
            $controller->store($request);
        }
        catch (TestException $exception)
        {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);
        
        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);
        
        $currentGenre = Genre::find($this->genre->id);
        $hasError = false;
        try 
        {
            $controller->update($request, $this->genre->id);
        }
        catch (TestException $exception)
        {
            $this->assertCount(1, Genre::all());
            $updatedGenre = Genre::find($this->genre->id);
            $this->assertEquals($currentGenre->name, $updatedGenre->name);
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    //TODO: testRollbackUpdate PENDING

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
