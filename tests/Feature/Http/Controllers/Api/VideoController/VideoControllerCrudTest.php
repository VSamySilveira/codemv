<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Video;
use App\Models\Category;
use App\Models\Genre;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves;

    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
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

    public function testSave()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $newSendData = array_merge($this->sendData, [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);

        $data = [
            [
                'send_data' => $newSendData,
                'test_data' => array_merge($this->sendData, ['opened' => false])
            ],
            [
                'send_data' => array_merge($newSendData, ['opened' => true]),
                'test_data' => array_merge($this->sendData, ['opened' => true])
            ],
            [
                'send_data' => array_merge($newSendData, ['rating' => Video::RATING_LIST[1]]),
                'test_data' => array_merge($this->sendData, ['rating' => Video::RATING_LIST[1]])
            ]
        ];

        foreach ($data as $key => $value)
        {
            $response = $this->assertStore(
                $value['send_data'],
                array_merge($value['test_data'], ['deleted_at' => null])
            );
            $response->assertJsonStructure([
                'created_at', 
                'updated_at'
            ]);
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);

            $response = $this->assertUpdate(
                $value['send_data'],
                array_merge($value['test_data'], ['deleted_at' => null])
            );
            $response->assertJsonStructure([
                'created_at', 
                'updated_at'
            ]);
            $this->assertHasCategory($response->json('id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genres_id'][0]);
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
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $data = [
                    [
                        'send_data' => $this->sendData + [
                            'categories_id' => [ $category->id],
                            'genres_id' => [ $genre->id]
                        ],
                        'test_data' => $this->sendData + ['opened' => false]
                    ],
                    [
                        'send_data' => $this->sendData + [
                            'opened' => true,
                            'categories_id' => [ $category->id],
                            'genres_id' => [ $genre->id]
                        ],
                        'test_data' => $this->sendData + ['opened' => true]
                    ],
                    [
                        'send_data' => $this->sendData + [
                            'rating' => Video::RATING_LIST[1],
                            'categories_id' => [ $category->id],
                            'genres_id' => [ $genre->id]
                        ],
                        'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
                    ]
        ];

        foreach ($data as $key => $value)
        {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['categories_id'][0]
            );
            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genres_id'][0]
            );

            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['categories_id'][0]
            );
            $this->assertHasGenre(
                $response->json('id'),
                $value['send_data']['genres_id'][0]
            );
        }
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);
    }
}
