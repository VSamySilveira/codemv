<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class BaseVideoTestCase extends TestCase
{
    use DatabaseMigrations;

    protected $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'title' => 'Test1',
            'description' => 'Description1',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90
        ];
    }
}