<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller
{
    protected $paginationSize = 15;
    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();
    protected abstract function resource();
    protected abstract function resourceCollection();

    public function index()
    {
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);
        $resourceCollectionClass = $this->resourceCollection();
        $reflactionClass = new \ReflectionClass($this->resourceCollection());
        return $reflactionClass->isSubclassOf(ResourceCollection::class)
            ? new $resourceCollectionClass($data)
            : $resourceCollectionClass::collection($data);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $object = $this->model()::create($validatedData);
        $object->refresh();
        $resource = $this->resource();
        return new $resource($object);
    }

    public function show($id)
    {
        $object = $this->findOrFail($id);
        $resource = $this->resource();
        return new $resource($object);
    }

    public function update(Request $request, $id)
    {
        $object = $this->findOrFail($id);
        $validatedData = $this->validate($request, $this->rulesUpdate());
        $object->update($validatedData);
        $resource = $this->resource();
        return new $resource($object);
    }

    public function destroy($id)
    {
        $object = $this->findOrFail($id);
        $object->delete();
        return response()->noContent();
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }
}
