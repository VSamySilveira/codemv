<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;

use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;

class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|min:2|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => ['required','array','exists:genres,id,deleted_at,NULL'],
            'video_file' => 'mimetypes:video/mp4|max:1024' //1Mb
        ];
    }

    public function store(Request $request)
    {
        /** @var Video $object */
        $this->addRuleIfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesStore());
        $object = $this->model()::create($validatedData);
        $object->refresh();
        return $object;
    }

    public function update(Request $request, $id)
    {
        /** @var Video $object */
        $object = $this->findOrFail($id);
        $this->addRuleIfGenreHasCategories($request);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $object->update($validatedData);
        return $object;
    }

    protected function addRuleIfGenreHasCategories($request)
    {
        $categoriesId = $request->get('categories_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($categoriesId);
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
