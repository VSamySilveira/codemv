<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;

use App\Models\Video;
use App\Rules\IsValidVideoCategory;

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
            'categories_id' => ['required', 'array','exists:categories,id', new IsValidVideoCategory],
            'genres_id' => 'required|array|exists:genres,id'
        ];
    }

    public function store(Request $request)
    {
        /** @var Video $object */
        $validatedData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $object = \DB::transaction(function () use ($request, $validatedData, $self) {
            $object = $this->model()::create($validatedData);
            $self->handleRelations($object, $request);
            return $object;
        });
        $object->refresh();
        return $object;
    }

    public function update(Request $request, $id)
    {
        /** @var Video $object */
        $object = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $object = \DB::transaction(function () use ($request, $validatedData, $self, $object) {
            $object->update($validatedData);
            $self->handleRelations($object, $request);
            return $object;
        });
        return $object;
    }

    protected function handleRelations(Video $video, Request $request)
    {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
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
