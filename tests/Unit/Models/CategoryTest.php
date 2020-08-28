<?php

namespace Tests\Unit\Models;
use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use phpDocumentor\Reflection\Types\Void_;

# Classe específica                     - ./vendor/bin/phpunit tests/Unit/CategoryTest.php
# Método específico em um arquivo       - ./vendor/bin/phpunit --filter testTraits tests/Unit/CategoryTest.php
# Método específico em uma classe       - ./vendor/bin/phpunit --filter CategoryTest::testTraits

class CategoryTest extends TestCase
{
    private $category;

    //Método executado apenas 1x antes dos testes
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    //Método executado antes de cada teste
    protected function setUp(): void
    {
            parent::setUp();
            $this->category = new Category();
    }

    //Método executado após cada teste
    protected function tearDown(): void
    {

        parent::tearDown();
    }

    //Método executado apenas 1x após todos os testes
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    public function testFillable()
    {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testTraits()
    {
        $expectedTraits = [
            SoftDeletes::class, Uuid::class
        ];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($expectedTraits, $categoryTraits);
    }

    public function testCasts()
    {
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $this->assertEquals($casts, $this->category->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->category->incrementing);
    }

    public function testDates()
    {
        $expectedDates = ['deleted_at','created_at','updated_at'];
        $categoryDates = $this->category->getDates();
        $this->assertCount(count($expectedDates), $categoryDates);
        foreach ($categoryDates as $date) {
            $this->assertContains($date, $expectedDates);
        }
    }

    public function testTableName()
    {
        $this->assertEquals('categories', $this->category->getTable());
    }
}
