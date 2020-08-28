<?php

namespace Tests\Feature\Models;

use \App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class CategoryTest extends TestCase
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
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);

        $categoryKeys = array_keys($categories->first()->getAttributes());
        $expectedKeys = ['id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'];
        $this->assertEqualsCanonicalizing($expectedKeys, $categoryKeys);
    }

    public function testCreate()
    {
        $category = Category::create(['name' => 'Test1']);
        $category->refresh();
        //Uuid Field
        $this->assertTrue($this->isValidUuid4($category->id));
        
        //Name Field
        $this->assertEquals('Test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        //Description Field
        $category = Category::create(['name' => 'Test1', 'description' => null]);
        $this->assertNull($category->description);
        $category = Category::create(['name' => 'Test1', 'description' => 'Teste Category']);
        $this->assertEquals('Teste Category', $category->description);

        //Is Active Field
        $category = Category::create(['name' => 'Test1', 'is_active' => false]);
        $this->assertFalse($category->is_active);
        $category = Category::create(['name' => 'Test1', 'is_active' => true]);
        $this->assertTrue($category->is_active);

    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create(['description' => 'Teste Category', 'is_active' => false])->first();
        $data = [
            'name' => 'Test2', 
            'description' => 'Teste Category Updated',
            'is_active' => true
        ];
        $category->update($data);

        foreach($data as $key => $value){
            $this->assertEquals($value, $category->{$key});
        }

        $category->update(['description' => null]);
        $this->assertNull($category->description);
    }

    public function testDelete()
    {
        $categories = factory(Category::class, 5)->create()->first();
        $category = $categories->first();
        $category_uuid = $category->id;
        $category->delete();

        $categories = Category::all();

        $this->assertCount(4, $categories);

        $categories = Category::onlyTrashed()->get();
        $this->assertCount(1, $categories);
        $this->assertEquals($category_uuid, $categories[0]->id);
    }

}
