<?php

namespace Tests\Unit\Models;
use App\Models\Category;
use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;


# Classe específica                     - ./vendor/bin/phpunit tests/Unit/CategoryTest.php
# Método específico em um arquivo       - ./vendor/bin/phpunit --filter testTraits tests/Unit/CategoryTest.php
# Método específico em uma classe       - ./vendor/bin/phpunit --filter CategoryTest::testTraits

class UploadFilesUnitTest extends TestCase
{
    private $uploadObject;

    protected function setUp(): void
    {
            parent::setUp();
            $this->uploadObject = new UploadFilesStub();
    }

    public function testUploadFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadObject->uploadFile($file);
        \Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadObject->uploadFiles([$file1, $file2]);
        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file1->hashName()}");
    }

    public function testDeleteOldFiles()
    {
        \Storage::fake();
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
        \Storage::fake();
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
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadObject->uploadFiles([$file1, $file2]);

        $this->uploadObject->deleteFiles([$file1->hashName(), $file2]);
        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");
    }

    public function testExtractFiles()
    {
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertEquals(['file1' => 'test'], $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test 1', 'file2' => 'test 2'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => 'test 1', 'file2' => 'test 2'], $attributes);
        $this->assertCount(0, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = ['file1' => $file1, 'other' => 'test other'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'other' => 'test other'], $attributes);
        $this->assertCount(1, $files);
        $this->assertEquals([$file1], $files);

        $file2 = UploadedFile::fake()->create('video2.mp4');
        $attributes = ['file1' => $file1, 'file2' => $file2, 'other' => 'test other'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'file2' => $file2->hashName(), 'other' => 'test other'], $attributes);
        $this->assertCount(2, $files);
        $this->assertEquals([$file1, $file2], $files);

        $attributes = ['file1' => $file1, 'file2' => $file2, 'other' => $file1];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'file2' => $file2->hashName(), 'other' => $file1], $attributes);
        $this->assertCount(2, $files);
        $this->assertEquals([$file1, $file2], $files);
    }
}
