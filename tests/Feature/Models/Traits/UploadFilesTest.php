<?php

namespace Tests\Feature\Models\Traits;

use Tests\TestCase;
use Tests\Stubs\Models\UploadFilesStub;

abstract class UploadFilesTest extends TestCase
{
    private $object;

    protected function setUp(): void
    {
        parent::setUp();
        $this->object = new UploadFilesStub();
        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
    }

    public function testMakeOldFieldsOnSaving()
    {
        $this->object->fill([
            'name' => 'test',
            'file1' => 'test1.mp4',
            'file2' => 'test2.mp4'
        ]);
        $this->object->save();

        $this->assertCount(0, $this->object->oldFiles);

        $this->object->update([
            'name' => 'test_name',
            'file2' => 'test3.mp4'
        ]);

        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->object->oldFiles);
    }

    public function testMakeOldFilesNullObSaving()
    {
        $this->object->fill([
            'name' => 'test'
        ]);
        $this->object->save();

        $this->object->update([
            'name' => 'test_name',
            'file2' => 'test3.mp4'
        ]);

        $this->assertEqualsCanonicalizing([], $this->object->oldFiles);
    }
}