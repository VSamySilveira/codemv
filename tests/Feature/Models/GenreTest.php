<?php

namespace Tests\Feature\Models;

use \App\Models\Genre;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class GenreTest extends TestCase
{
    use DatabaseMigrations;
    private static function isValidUuid4( $uuid ) {
    
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            return false;
        }
    
        return true;
    }

    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);

        $genreKeys = array_keys($genres->first()->getAttributes());
        $expectedKeys = ['id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'];
        $this->assertEqualsCanonicalizing($expectedKeys, $genreKeys);
    }

    public function testCreate()
    {
        $genre = Genre::create(['name' => 'Test1']);
        $genre->refresh();
        //Uuid Field
        $this->assertTrue($this->isValidUuid4($genre->id));
        
        //Name Field
        $this->assertEquals('Test1', $genre->name);
        $this->assertTrue($genre->is_active);

        //Is Active Field
        $genre = Genre::create(['name' => 'Test1', 'is_active' => false]);
        $this->assertFalse($genre->is_active);
        $genre = Genre::create(['name' => 'Test1', 'is_active' => true]);
        $this->assertTrue($genre->is_active);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create(['is_active' => false])->first();
        $data = [
            'name' => 'Test2', 
            'is_active' => true
        ];
        $genre->update($data);

        foreach($data as $key => $value){
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        $genres = factory(Genre::class, 5)->create()->first();
        $genre = $genres->first();
        $genre_uuid = $genre->id;
        $genre->delete();

        $genres = Genre::all();

        $this->assertCount(4, $genres);

        $genres = Genre::onlyTrashed()->get();
        $this->assertCount(1, $genres);
        $this->assertEquals($genre_uuid, $genres[0]->id);
    }
}
