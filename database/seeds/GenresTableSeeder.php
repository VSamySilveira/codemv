<?php

use Illuminate\Database\Seeder;
use App\Models\Genre;
use App\Models\Category;

class GenresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        $genres = [
            ['name' => 'Ação'],
            ['name' => 'Animação'],
            ['name' => 'Aventura'],
            ['name' => 'Comédia'],
            ['name' => 'Drama'],
            ['name' => 'Documentário'],
            ['name' => 'Erótico'],
            ['name' => 'Fantasia'],
            ['name' => 'Faroeste'],
            ['name' => 'Ficção Científica'],
            ['name' => 'Guerra'],
            //['name' => 'Infantil'],
            ['name' => 'Musicais'],
            ['name' => 'Policial'],
            ['name' => 'Romance'],
            ['name' => 'Seriado'],
            ['name' => 'Suspense'],
            ['name' => 'Terror']
        ];
        foreach ($genres as $genre) {
            $categoriesId = $categories->random(5)->pluck('id')->toArray();
            Genre::create($genre)->categories()->attach($categoriesId);
        }
    }
}
