<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use App\Models\Image;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear un usuario específico
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Crear más usuarios
        $users = User::factory(4)->create(); // puedes cambiar la cantidad si deseas

        // Asignar 1 imagen a cada usuario (morphOne)
        $users->each(function ($user) {
            $user->image()->create(
                Image::factory()->make()->toArray()
            );
        });

        // Crear artículos
        $articles = Article::factory(12)->create();

        // Asignar 4 imágenes a cada artículo (morphMany)
        $articles->each(function ($article) {
            Image::factory(4)->create([
                'imageable_id' => $article->id,
                'imageable_type' => Article::class,
            ]);
        });
    }
}

