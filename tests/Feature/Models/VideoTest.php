<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    private $data;

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

    public function testList()
    {
        factory(Video::class)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);
        $videoKeys = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'title',
                'description',
                'year_launched',
                'opened',
                'rating',
                'duration',
                'created_at',
                'updated_at',
                'deleted_at'
            ],$videoKeys
        );
    }

    public function testCreateWithBasicFields()
    {
        $video = Video::create($this->data);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', array_merge($this->data, ['opened' => false]));

        $video = Video::create(array_merge($this->data, ['opened' => true]));
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', ['opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = Video::create(array_merge(
            $this->data,
            [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
            ]
        ));
        
        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testRollbackStore()
    {
        $hasError = false;
        try 
        {
            Video::create([
                'title' => 'Test1',
                'description' => 'Description1',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2]
            ]);
        }
        catch (QueryException $exception)
        {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;
        $hasError = false;
        try 
        {
            $video->update([
                'title' => 'Test1',
                'description' => 'Description1',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2]
            ]);
        }
        catch (QueryException $exception)
        {
            $this->assertDatabaseHas('videos', ['title' => $oldTitle]);
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testUpdateWithBasicFields()
    {
        $video = factory(Video::class)->create(
            ['opened' => false]
        );
        $video->update($this->data);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', array_merge($this->data, ['opened' => false]));

        $video->update(array_merge($this->data, ['opened' => true]));
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', array_merge($this->data, ['opened' => true]));
    }

    public function testUpdateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = factory(Video::class)->create();
        $video->update(array_merge(
            $this->data,
            [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
            ]
        ));
        
        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    protected function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);
    }

    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    public function testHandleRelations()
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, [
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);

        $video->categories()->delete();
        $video->genres()->delete();

        Video::handleRelations($video, [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);
        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[0]]
        ]);
        $this->assertHasCategory($video->id, $categoriesId[0]);

        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ]);
        $this->assertDatabaseMissing('category_video', [
            'video_id' => $video->id,
            'category_id' => $categoriesId[0]
        ]);
        $this->assertHasCategory($video->id, $categoriesId[1]);
        $this->assertHasCategory($video->id, $categoriesId[2]);
    }

    public function testSyncGenres()
    {
        $genresId = factory(Genre::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'genres_id' => [$genresId[0]]
        ]);
        $this->assertHasGenre($video->id, $genresId[0]);

        Video::handleRelations($video, [
            'genres_id' => [$genresId[1], $genresId[2]]
        ]);
        $this->assertDatabaseMissing('genre_video', [
            'video_id' => $video->id,
            'genre_id' => $genresId[0]
        ]);
        $this->assertHasGenre($video->id, $genresId[1]);
        $this->assertHasGenre($video->id, $genresId[2]);
    }

}