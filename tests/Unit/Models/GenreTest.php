<?php

namespace Tests\Unit\Models;
use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use phpDocumentor\Reflection\Types\Void_;

class GenreTest extends TestCase
{
    private $genre;

    //Método executado antes de cada teste
    protected function setUp(): void
    {
            parent::setUp();
            $this->genre = new Genre();
    }

    public function testFillable()
    {
        $fillable = ['name', 'is_active'];
        $this->assertEquals($fillable, $this->genre->getFillable());
    }

    public function testTraits()
    {
        $expectedTraits = [
            SoftDeletes::class, Uuid::class
        ];
        $genreTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($expectedTraits, $genreTraits);
    }

    public function testCasts()
    {
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $this->assertEquals($casts, $this->genre->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->genre->incrementing);
    }

    public function testDates()
    {
        $expectedDates = ['deleted_at','created_at','updated_at'];
        $genreDates = $this->genre->getDates();
        $this->assertEqualsCanonicalizing($expectedDates, $genreDates);
    }

    public function testTableName()
    {
        $this->assertEquals('genres', $this->genre->getTable());
    }
}
