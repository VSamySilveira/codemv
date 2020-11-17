<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;

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
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $this->sendData = [
            'title' => 'Test1',
            'description' => 'Description1',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ];
    }   

    protected function assertIfFilesUrlExists(Video $video, TestResponse $response)
    {
        $fileFields = Video::$fileFields;
        $data = $response->json('data');
        $data = array_key_exists(0, $data) ? $data[0] : $data;
        foreach ($fileFields as $field)
        {
            $file = $video->{$field};
            //EDIT:
            //If file is null, the Video Resource will bring null into _url field. So, assertEquals will not match if use \Storage::url direct into assertFunction. It has to be null if $file is null.
            $localUrl = is_null($file) ? null : \Storage::url($video->relativeFilePath($file));
            $this->assertEquals(
                $localUrl, 
                $data[$field . '_url']
            );
        }
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function routeStore()
    {
        return route(self::myRoutes['store']);
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
