<?php

namespace Tests\Unit\Models;
use App\Models\CastMember;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use phpDocumentor\Reflection\Types\Void_;

class CastMemberTest extends TestCase
{
    private $castMember;

    //Método executado antes de cada teste
    protected function setUp(): void
    {
            parent::setUp();
            $this->castMember = new CastMember();
    }

    public function testFillable()
    {
        $fillable = ['name', 'type'];
        $this->assertEquals($fillable, $this->castMember->getFillable());
    }

    public function testTraits()
    {
        $expectedTraits = [
            SoftDeletes::class, Uuid::class
        ];
        $castMemberTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($expectedTraits, $castMemberTraits);
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->castMember->incrementing);
    }

    public function testDates()
    {
        $expectedDates = ['deleted_at','created_at','updated_at'];
        $castMemberDates = $this->castMember->getDates();
        $this->assertCount(count($expectedDates), $castMemberDates);
        foreach ($castMemberDates as $date) {
            $this->assertContains($date, $expectedDates);
        }
    }

    public function testTableName()
    {
        $this->assertEquals('cast_members', $this->castMember->getTable());
    }
}
