<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private const myRoutes = [
        'index' => 'api.cast_members.index',
        'show' => 'api.cast_members.show',
        'store' => 'api.cast_members.store',
        'update' => 'api.cast_members.update',
        'destroy' => 'api.cast_members.destroy'
    ];

    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create();
    }

    ////////// TESTING FUNCTIONS ///////////
    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route(self::myRoutes['show'], ['cast_member' => $this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['type' => null];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['type' => 'x'];
        $this->assertInvalidationInStoreAction($data, 'numeric');
        $this->assertInvalidationInUpdateAction($data, 'numeric');

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testStore()
    {
        $data = ['name' => 'Test1', 'type' => 1];
        $response = $this->assertStore(
            $data, 
            $data + ['is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'Test1',
            'type' => 2,
            'is_active' => false
        ];
        $this->assertStore(
            $data, 
            $data
        );
    }

    public function testUpdate()
    {
        $this->castMember = factory(CastMember::class)->create([
            'is_active' => false
        ]);

        $data = [
            'name' => 'Test1',
            'type' => 1,
            'is_active' => true
        ];
        $response = $this->assertUpdate(
            $data,
            $data
        );
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route(self::myRoutes['destroy'], ['cast_member' => $this->castMember->id]));
        $response->assertStatus(204);
        $uuid = $this->castMember->id;
        //Check if deleted
        $castMember = CastMember::find($uuid);
        $this->assertNull($castMember);
        //Check if trashed
        $castMember = CastMember::onlyTrashed()->find($uuid);
        $this->assertNotNull($castMember);
    }

    /////////// AUXILIARY FUNCTIONS ///////////
    protected function routeStore()
    {
        return route(self::myRoutes['index']);
    }

    protected function routeUpdate()
    {
        return route(self::myRoutes['update'], ['cast_member' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }
}
