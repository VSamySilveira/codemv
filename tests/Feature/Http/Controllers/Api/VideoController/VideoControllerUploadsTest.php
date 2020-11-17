<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Tests\Traits\TestValidations;
use Tests\Traits\TestUploads;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestUploads;

    public function testInvalidationVideoField()
    {
        //Get max size without defining default value on env function, so, during tests it must throw exception case any miss configuration on env
        $this->assertInvalidationFile('video_file', 'mp4', env('MAX_VIDEO_FILE_SIZE'), 'mimetypes', [ 'values' => 'video/mp4']);
        $this->assertInvalidationFile('thumb_file', 'png', env('MAX_THUMB_FILE_SIZE'), 'image');
        $this->assertInvalidationFile('banner_file', 'jpg', env('MAX_BANNER_FILE_SIZE'), 'image');
        $this->assertInvalidationFile('trailer_file', 'mp4', env('MAX_TRAILER_FILE_SIZE'), 'mimetypes', [ 'values' => 'video/mp4']);
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('POST', $this->routeStore(), $this->sendData + $files);
        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);
        $video = Video::find($response->json('data.id'));
        $this->assertIfFilesUrlExists($video, $response);
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + $files);
        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);
        $video = Video::find($response->json('data.id'));
        $this->assertIfFilesUrlExists($video, $response);

        $newFiles = [
            'thumb_file' => UploadedFile::fake()->image("thumb_file2.png"),
            'video_file' => UploadedFile::fake()->create("video_file2.mp4")
        ];
        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + $newFiles);
        $response->assertStatus(200);
        $newDataFiles = Arr::except($files, ['thumb_file','video_file']) + $newFiles;
        $this->assertFilesOnPersist($response, $newDataFiles);
        
        $video = Video::find($response->json('data.id'));
        $this->assertIfFilesUrlExists($video, $response);

        \Storage::assertMissing("{$video->relativeFilePath($files['thumb_file']->hashName())}");
        \Storage::assertMissing("{$video->relativeFilePath($files['video_file']->hashName())}");
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function getFiles()
    {
        return [
            'thumb_file' => UploadedFile::fake()->image("thumb_file.png"),
            'banner_file' => UploadedFile::fake()->image("banner_file.png"),
            'trailer_file' => UploadedFile::fake()->create("trailer_file.mp4"),
            'video_file' => UploadedFile::fake()->create("video_file.mp4")
        ];
    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id') ?? $response->json('data.id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }
    
}
