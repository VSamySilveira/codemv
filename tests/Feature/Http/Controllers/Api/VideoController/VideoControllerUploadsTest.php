<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestValidations;
use Tests\Traits\TestUploads;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestUploads;

    public function testInvalidationVideoField()
    {
        //Get max size without defining default value on env function, so, during tests it can throw exception case any miss configuration on env
        $this->assertInvalidationFile('video_file', 'mp4', env('MAX_VIDEO_SIZE'), 'mimetypes', [ 'values' => 'video/mp4']);
        $this->assertInvalidationFile('thumb_file', 'png', env('MAX_THUMB_SIZE'), 'image');
        $this->assertInvalidationFile('banner_file', 'jpg', env('MAX_BANNER_SIZE'), 'image');
        $this->assertInvalidationFile('trailer_file', 'mp4', env('MAX_TRAILER_SIZE'), 'mimetypes', [ 'values' => 'video/mp4']);
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json('POST', $this->routeStore(), $this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ] + $files);
        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file)
        {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ] + $files);
        $response->assertStatus(200);
        $id = $response->json('id');
        foreach ($files as $file)
        {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create("video_file.mp4")
        ];
    }

    
}
