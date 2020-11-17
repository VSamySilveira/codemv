<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Support\Arr;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestResources;

    private $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'rating',
        'duration',
        'opened',
        'thumb_file_url',
        'banner_file_url',
        'video_file_url',
        'trailer_file_url',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ],
        'genres' => [
            '*' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]
    ];

    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'meta' => [],
                'links' => []
            ]);
        
        $this->assertResource($response, VideoResource::collection(collect([$this->video])));
        $this->assertIfFilesUrlExists($this->video, $response);
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
        
        $this->assertResource($response, new VideoResource(Video::find($response->json('data.id'))));
        $this->assertIfFilesUrlExists($this->video, $response);
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
        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            [
                'data' => ['categories_id' => 'x'],
                'rule' => 'array'
            ],
            [
                'data' => ['categories_id' => [50]],
                'rule' => 'exists'
            ],
            [
                'data' => ['categories_id' => [$category->id]],
                'rule' => 'exists'
            ]
        ];
        $this->assertInvalidationSaveRelationshipFields($data);
    }

    public function testInvalidationGenresIdField()
    {
        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            [
                'data' => ['genres_id' => 'x'],
                'rule' => 'array'
            ],
            [
                'data' => ['genres_id' => [50]],
                'rule' => 'exists'
            ],
            [
                'data' => ['genres_id' => [$genre->id]],
                'rule' => 'exists'
            ]
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

    public function testSaveWithoutFiles()
    {
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);
        $data = [
                    [
                        'send_data' => $this->sendData,
                        'test_data' => $testData + ['opened' => false]
                    ],
                    [
                        'send_data' => $this->sendData + [
                            'opened' => true
                        ],
                        'test_data' => $testData + ['opened' => true]
                    ],
                    [
                        'send_data' => $this->sendData + [
                            'rating' => Video::RATING_LIST[1]
                        ],
                        'test_data' => $testData + ['rating' => Video::RATING_LIST[1]]
                    ]
        ];

        foreach ($data as $key => $value)
        {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource($response, new VideoResource(Video::find($response->json('data.id'))));
            $this->assertIfFilesUrlExists($this->video, $response);

            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource($response, new VideoResource(Video::find($response->json('data.id'))));
            $this->assertIfFilesUrlExists($this->video, $response);
        }
    }
}
