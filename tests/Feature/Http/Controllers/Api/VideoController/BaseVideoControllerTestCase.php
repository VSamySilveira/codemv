<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected const myRoutes = [
        'index' => 'api.videos.index',
        'show' => 'api.videos.show',
        'store' => 'api.videos.store',
        'update' => 'api.videos.update',
        'destroy' => 'api.videos.destroy'
    ];

    protected $video;
    protected $sendData;

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
