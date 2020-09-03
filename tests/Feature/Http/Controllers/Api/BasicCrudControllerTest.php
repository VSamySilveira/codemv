<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Illuminate\Http\Request;
use ReflectionClass;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class BasicCrudControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        $categoryStub = CategoryStub::create(['name' => 'Test Name', 'description' => 'Test Description']);
        $result = $this->controller->index()->toArray();
        $this->assertEquals([$categoryStub->toArray()], $result);
    }

    public function testInvalidationDataInStructure()
    {
        $this->expectException(ValidationException::class);

        //Mockery php
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);
        $this->controller->store($request);
    }

    public function testStore()
    {
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'Name Test', 'description' => 'Desc Test']);
        $object = $this->controller->store($request);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(), 
            $object->toArray()
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        /** @var CategoryStub $category */
        $categoryStub = CategoryStub::create(['name' => 'Test Name', 'description' => 'Test Description']);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [$categoryStub->id]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testShow()
    {
        $categoryStub = CategoryStub::create(['name' => 'Test Name', 'description' => 'Test Description']);
        $resultObject = $this->controller->show($categoryStub->id);
        $storedObject = CategoryStub::find(1);
        $this->assertEquals($resultObject->toArray(), $storedObject->toArray());
    }

    public function testUpdate()
    {
        $categoryStub = CategoryStub::create(['name' => 'Test Name', 'description' => 'Test Description']);
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'New Name Test', 'description' => 'New Desc Test']);
        $resultObject = $this->controller->update($request, $categoryStub->id);
        $storedObject = CategoryStub::find(1);
        $this->assertEquals($resultObject->toArray(), $storedObject->toArray());
    }

    public function testDestroy()
    {
        $categoryStub = CategoryStub::create(['name' => 'Test Name', 'description' => 'Test Description']);
        $resultResponse = $this->controller->destroy($categoryStub->id);
        //$resultResponse->assertStatus(204);
        $this->createTestResponse($resultResponse)->assertStatus(204);
        //Check if deleted
        $newCategory = CategoryStub::find($categoryStub->id);
        $this->assertNull($newCategory);
        //Check if trashed - Stub is not using SoftDelete
        //$newCategory = CategoryStub::onlyTrashed()->find($categoryStub->id);
        //$this->assertNotNull($newCategory);
    }
}
