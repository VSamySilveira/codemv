<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;

use App\Models\Genre;

class GenreController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => 'required|min:2|max:255',
            'is_active' => 'boolean',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
        ];
    }

    public function store(Request $request)
    {
        /** @var Genre $object */
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
        /** @var Genre $object */
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

    protected function handleRelations(Genre $genre, Request $request)
    {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function model()
    {
        return Genre::class;
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
