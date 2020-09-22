<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Genre;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
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
    private $sendData;

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
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['genre' => $this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
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
        $data = [
            [
                'data' => ['categories_id' => 'x'],
                'rule' => 'array'
            ],
            [
                'data' => ['categories_id' => [50]],
                'rule' => 'exists'
            ],
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
        $category = factory(Category::class)->create();
        $newSendData = array_merge($this->sendData, [
            'categories_id' => [$category->id]
        ]);
        $data = [
            [
                'send_data' => $newSendData,
                'test_data' => array_merge($this->sendData, ['is_active' => true])
            ],
            [
                'send_data' => array_merge($newSendData, ['is_active' => false]),
                'test_data' => array_merge($this->sendData, ['is_active' => false])
            ]
        ];

        foreach ($data as $key => $value)
        {
            $response = $this->assertStore(
                $value['send_data'],
                array_merge($value['test_data'], ['deleted_at' => null])
            );
            $response->assertJsonStructure([
                'created_at', 
                'updated_at'
            ]);
            $response = $this->assertUpdate(
                $value['send_data'],
                array_merge($value['test_data'], ['deleted_at' => null])
            );
            $response->assertJsonStructure([
                'created_at', 
                'updated_at'
            ]);
        }
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

        try 
        {
            $controller->store($request);
        }
        catch (TestException $exception)
        {
            $this->assertCount(1, Genre::all());
        }
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
        try 
        {
            $controller->update($request, $this->genre->id);
        }
        catch (TestException $exception)
        {
            $this->assertCount(1, Genre::all());
            $updatedGenre = Genre::find($this->genre->id);
            $this->assertEquals($currentGenre->name, $updatedGenre->name);
        }
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
