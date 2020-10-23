<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Traits\Uuid, Traits\UploadFiles;

    const RATING_LIST = [
        'L',
        '10',
        '12',
        '16',
        '18'
    ];

    //protected $table = 'videos';
    protected $fillable = [
        'title', 
        'description', 
        'year_launched', 
        'opened', 
        'rating', 
        'duration'
    ];
    protected $dates = ["deleted_at"];
    
    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer'
    ];

    public $incrementing = false;

    public static $fileFields = ['video_file'];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $object = static::query()->create($attributes);
            static::handleRelations($object, $attributes);
            $object->uploadFiles($files);
            \DB::commit();
            return $object;
        } catch (\Exception $err) {
            if(isset($object)) {
                //TODO: Excluir arquivos de upload
            }
            \DB::rollBack();
            throw $err;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved)
            {
                //TODO: Novos arquivos de upload aqui
                //TODO: Exclusao de arquivos antigos aqui
            }
            \DB::commit();
            return $saved;
        } catch (\Exception $err) {
            //TODO: Excluir arquivos de upload
            \DB::rollBack();
            throw $err;
        }
    }

    public static function handleRelations(Video $video, array $attributes)
    {
        if(isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }
        if(isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }
    
    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    protected function uploadDir()
    {
        return $this->id;
    }
}
