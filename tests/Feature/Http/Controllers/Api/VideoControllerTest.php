<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Exception;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private const myRoutes = [
        'index' => 'api.videos.index',
        'show' => 'api.videos.show',
        'store' => 'api.videos.store',
        'update' => 'api.videos.update',
        'destroy' => 'api.videos.destroy'
    ];

    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = factory(Video::class)->create(['opened' => false]);

        $this->sendData = [
            'title' => 'Test1',
            'description' => 'Description1',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90
        ];
    }

    ////////// TESTING FUNCTIONS ///////////
    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = ['title' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationMin()
    {
        $data = ['title' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'min.string', ['min' => 2]);
        $this->assertInvalidationInUpdateAction($data, 'min.string', ['min' => 2]);
    }

    public function testInvalidationInteger()
    {
        $data = ['duration' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = ['year_launched' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = ['opened' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = ['rating' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
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

    public function testInvalidationGenresIdField()
    {
        $data = [
            [
                'data' => ['genres_id' => 'x'],
                'rule' => 'array'
            ],
            [
                'data' => ['genres_id' => [50]],
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
        $genre = factory(Genre::class)->create();
        $newSendData = array_merge($this->sendData, [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);
        $data = [
            [
                'send_data' => $newSendData,
                'test_data' => array_merge($this->sendData, ['opened' => false])
            ],
            [
                'send_data' => array_merge($newSendData, ['opened' => true]),
                'test_data' => array_merge($this->sendData, ['opened' => true])
            ],
            [
                'send_data' => array_merge($newSendData, ['rating' => Video::RATING_LIST[1]]),
                'test_data' => array_merge($this->sendData, ['rating' => Video::RATING_LIST[1]])
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
        $controller = \Mockery::mock(VideoController::class)
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
            $this->assertCount(1, Video::all());
        }
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->video);

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
        
        $currentVideo = Video::find($this->video->id);
        try 
        {
            $controller->update($request, $this->video->id);
        }
        catch (TestException $exception)
        {
            $this->assertCount(1, Video::all());
            $updatedVideo = Video::find($this->video->id);
            $this->assertEquals($currentVideo->title, $updatedVideo->title);
        }
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::myRoutes['destroy'], ['video' => $this->video->id]));
        $response->assertStatus(204);
        $uuid = $this->video->id;
        //Check if deleted
        $video = Video::find($uuid);
        $this->assertNull($video);
        //Check if trashed
        $video = Video::onlyTrashed()->find($uuid);
        $this->assertNotNull($video);
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function routeStore()
    {
        return route(self::myRoutes['index']);
    }

    protected function routeUpdate()
    {
        return route(self::myRoutes['update'], ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
