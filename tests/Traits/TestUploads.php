<?php
declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Video;
use Illuminate\Http\UploadedFile;

trait TestUploads
{
    protected function assertInvalidationFile($field, $extension, $maxSize, $rule, $ruleParams = [])
    {
        $routes = [
            [
                'method' => 'POST',
                'route' => $this->routeStore()
            ],
            [
                'method' => 'PUT',
                'route' => $this->routeUpdate()
            ]
        ];

        foreach ($routes as $route) {

            $file = ($extension == "mp4" ? UploadedFile::fake()->create("$field.1$extension") : UploadedFile::fake()->image("$field.1$extension"));
            $response = $this->json($route['method'], $route['route'], [$field => $file]);
            //var_dump($response);
            $this->assertInvalidationFields($response, [$field], $rule, $ruleParams);

            $file = ($extension == "mp4" ? UploadedFile::fake()->create("$field.$extension")->size($maxSize + 1) : UploadedFile::fake()->image("$field.$extension")->size($maxSize + 1));
            $response = $this->json($route['method'], $route['route'], [$field => $file]);
            $this->assertInvalidationFields($response, [$field], 'max.file', ['max' => $maxSize]);
        }
    }

    protected function assertFilesExistsInStorage($model, array $files)
    {
        foreach ($files as $file)
        {
            \Storage::assertExists($model->relativeFilePath($file->hashName()));
        }
    }
}