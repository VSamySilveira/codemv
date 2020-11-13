<?php

namespace Tests\Prod\Models;
use Illuminate\Http\UploadedFile;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;
use Tests\Traits\TestStorages;
use Tests\Traits\TestProd;

class UploadFilesProdTest extends TestCase
{
    use TestProd, TestStorages;

    private $uploadObject;

    protected function setUp(): void
    {
            parent::setUp();
            $this->skipTestIfNotProd('Production Only Test');
            $this->uploadObject = new UploadFilesStub();
            \Config::set('filesystems.default', 'gcs');
            $this->deleteAllFiles();
    }

    public function testUploadFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadObject->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadObject->uploadFiles([$file1, $file2]);
        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file1->hashName()}");
    }

    public function testDeleteOldFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4')->size(1);
        $file2 = UploadedFile::fake()->create('video2.mp4')->size(1);
        $this->uploadObject->uploadFiles([$file1, $file2]);
        $this->uploadObject->deleteOldFiles();
        $this->assertCount(2, \Storage::allFiles());

        $this->uploadObject->oldFiles = [$file1->hashName()];
        $this->uploadObject->deleteOldFiles();
        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadObject->uploadFile($file);
        $filename = $file->hashName();
        $this->uploadObject->deleteFile($filename);
        \Storage::assertMissing("1/{$filename}");

        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadObject->uploadFile($file);
        $this->uploadObject->deleteFile($file);
        \Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadObject->uploadFiles([$file1, $file2]);

        $this->uploadObject->deleteFiles([$file1->hashName(), $file2]);
        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");
    }
}
