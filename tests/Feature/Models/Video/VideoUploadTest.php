<?php

namespace Tests\Feature\Models\Video;

use Illuminate\Http\UploadedFile;
use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Tests\Exceptions\TestException;

class VideoUploadTest extends BaseVideoTestCase
{
    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'banner_file' => UploadedFile::fake()->image('banner.jpg'),
                'trailer_file' => UploadedFile::fake()->create('trailer.mp4'),
                'video_file' => UploadedFile::fake()->create('thumb.mp4')
            ]
        );
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
    }

    public function testCreateIfRollbackFiles()
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });
        $hasError = false;

        try {
            Video::create(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'banner_file' => UploadedFile::fake()->image('banner.jpg'),
                    'trailer_file' => UploadedFile::fake()->create('trailer.mp4'),
                    'video_file' => UploadedFile::fake()->create('thumb.mp4')
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        $thumbFile = UploadedFile::fake()->image('thumb.png');
        $bannerFile = UploadedFile::fake()->image('banner.png');
        $trailerFile = UploadedFile::fake()->create('trailer.mp4');
        $videoFile = UploadedFile::fake()->create('video.mp4');
        $video->update(array_merge($this->data, [
            'thumb_file' => $thumbFile,
            'banner_file' => $bannerFile,
            'trailer_file' => $trailerFile,
            'video_file' => $videoFile

        ]));
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");

        $newVideoFile = UploadedFile::fake()->create('video2.mp4');
        $video->update(array_merge($this->data, [
            'video_file' => $newVideoFile
        ]));
        \Storage::assertExists("{$video->id}/{$thumbFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$bannerFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$trailerFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$videoFile->hashName()}");
    }

    public function testUpdateIfRollbackFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });
        $hasError = false;

        try {
            $video->update(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.png'),
                    'banner_file' => UploadedFile::fake()->image('banner.png'),
                    'trailer_file' => UploadedFile::fake()->create('trailer.mp4'),
                    'video_file' => UploadedFile::fake()->create('thumb.mp4')
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }
}