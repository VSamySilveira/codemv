<?php

namespace Tests\Feature\Models;

use \App\Models\CastMember;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class CastMemberTest extends TestCase
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
        factory(CastMember::class, 1)->create();
        $castMembers = CastMember::all();
        $this->assertCount(1, $castMembers);

        $castMemberKeys = array_keys($castMembers->first()->getAttributes());
        $expectedKeys = ['id', 'name', 'type', 'is_active', 'created_at', 'updated_at', 'deleted_at'];
        $this->assertEqualsCanonicalizing($expectedKeys, $castMemberKeys);
    }

    public function testCreate()
    {
        $castMember = CastMember::create(['name' => 'Test1', 'type' => 1]);
        $castMember->refresh();
        
        $this->assertTrue($this->isValidUuid4($castMember->id));
        $this->assertEquals('Test1', $castMember->name);
        $this->assertEquals(1, $castMember->type);
        $this->assertTrue($castMember->is_active);

        $castMember = CastMember::create(['name' => 'Test1', 'type' => 2, 'is_active' => false]);
        $this->assertNull($castMember->description);
        $this->assertEquals(2, $castMember->type);
        $this->assertFalse($castMember->is_active);
    }

    public function testUpdate()
    {
        $castMember = factory(CastMember::class)->create(['type' => 1, 'is_active' => false])->first();
        $data = [
            'name' => 'Test2', 
            'type' => 2,
            'is_active' => true
        ];
        $castMember->update($data);

        foreach($data as $key => $value){
            $this->assertEquals($value, $castMember->{$key});
        }
    }

    public function testDelete()
    {
        $castMembers = factory(CastMember::class, 5)->create()->first();
        $castMember = $castMembers->first();
        $castMember_uuid = $castMember->id;

        //SoftDelete Test
        $castMember->delete();
        $castMembers = CastMember::all();
        $castMember = CastMember::find($castMember_uuid);
        $this->assertCount(4, $castMembers);
        $this->assertNull($castMember);

        //Trash Test
        $castMembers = CastMember::onlyTrashed()->get();
        $this->assertCount(1, $castMembers);
        $castMember = $castMembers->first();
        $this->assertEquals($castMember_uuid, $castMember->id);

        //Restore Test
        $castMember->restore();
        $castMembers = CastMember::onlyTrashed()->get();
        $this->assertCount(0, $castMembers);

        $castMembers = CastMember::all();
        $castMember = CastMember::find($castMember_uuid);
        $this->assertCount(5, $castMembers);
        $this->assertEquals($castMember_uuid, $castMember->id);
    }
}
