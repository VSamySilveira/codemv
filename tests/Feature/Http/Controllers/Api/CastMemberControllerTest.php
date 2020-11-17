<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use PhpParser\Node\Stmt\Foreach_;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private const myRoutes = [
        'index' => 'api.cast_members.index',
        'show' => 'api.cast_members.show',
        'store' => 'api.cast_members.store',
        'update' => 'api.cast_members.update',
        'destroy' => 'api.cast_members.destroy'
    ];

    private $serializedFields = [
        'id',
        'name',
        'type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    ////////// TESTING FUNCTIONS ///////////
    public function testIndex()
    {
        $response = $this->get(route(self::myRoutes['index']));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => $this->serializedFields
                    ],
                    'meta' => [],
                    'links' => []
                ]
            );
    }

    public function testShow()
    {
        $response = $this->json('GET', route(self::myRoutes['show'], ['cast_member' => $this->castMember->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJsonFragment($this->castMember->toArray());
        
        $this->assertResource($response, new CastMemberResource($this->castMember));
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'type' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'type' => 's'
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            [
                'name' => 'Test1', 
                'type' => CastMember::TYPE_DIRECTOR
            ],
            [
                'name' => 'Test2', 
                'type' => CastMember::TYPE_ACTOR
            ]
        ];
        foreach ($data as $key => $value)
        {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertResource($response, new CastMemberResource(CastMember::find($response->json('data.id'))));
        }
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'Test1',
            'type' => CastMember::TYPE_ACTOR
        ];
        $response = $this->assertUpdate(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);
        $this->assertResource($response, new CastMemberResource(CastMember::find($response->json('data.id'))));
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
