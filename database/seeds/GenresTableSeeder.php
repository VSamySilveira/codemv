<?php

use Illuminate\Database\Seeder;

class GenresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
            App\Models\Genre::create($genre);
        }
    }
}
