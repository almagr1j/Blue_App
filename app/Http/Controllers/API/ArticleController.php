<?php


namespace App\Http\Controllers\API;


use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ArticleCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;


class ArticleController extends Controller
{
    public function index()
    {
        try {
            $articles = Article::with(['images', 'user'])->get();
            return new ArticleCollection($articles);
        } catch (\Exception $e) {
            Log::error('Error fetching articles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener articulos',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Article $article)
    {
        try {
            return new ArticleResource($article->load(['images', 'user']));
        } catch (\Exception $e) {
            Log::error('Error fetching article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el articulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'images' => 'sometimes|array|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);


            $validated['user_id'] = $request->user()->id;
            $article = Article::create($validated);


            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('images', 'public');
                    $article->images()->create([
                        'url' => Storage::url($path)
                    ]);
                }
            }


            return response()->json([
                'success' => true,
                'data' => new ArticleResource($article->load(['images', 'user']))
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating article: '. $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear articulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
public function update(Request $request, Article $article)
{
    try {
        // Autorización con Gate
        Log::info('Usuario autenticado: ' . $request->user()->id . ', Artículo user_id: ' . $article->user_id);
        Gate::authorize('update', $article);


        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'images' => 'sometimes|array|max:5',
            'images.*' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
            'remove_image_ids' => 'sometimes|array',
            'remove_image_ids.*' => [
                'integer',
                Rule::exists('images', 'id')->where('imageable_id', $article->id)
            ],
        ]);


        $article->update($validated);


        if ($request->has('remove_image_ids')) {
            $imagesToDelete = $article->images()->whereIn('id', $request->remove_image_ids)->get();
            foreach ($imagesToDelete as $image) {
                $path = str_replace('/storage/', '', parse_url($image->url, PHP_URL_PATH));
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                $image->delete();
            }
        }


        return response()->json([
            'success' => true,
            'data' => new ArticleResource($article->load(['images', 'user']))
        ]);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'No autorizado para actualizar este artículo',
            'error' => $e->getMessage()
        ], 403);
    } catch (\Exception $e) {
        Log::error('Error updating article: '. $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar articulo',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function destroy(Request $request, Article $article)
{
    try {
        // Autorización con Gate
        Log::info('Usuario autenticado: ' . $request->user()->id . ', Artículo user_id: ' . $article->user_id);
        Gate::authorize('delete', $article);


        foreach ($article->images as $image) {
            $path = str_replace('/storage/', '', parse_url($image->url, PHP_URL_PATH));
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $image->delete();
        }


        $article->delete();


        return response()->json([
            'success' => true,
            'message' => 'Articulo eliminado correctamente'
        ]);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'No autorizado para eliminar este artículo',
            'error' => $e->getMessage()
        ], 403);
    } catch (\Exception $e) {
        Log::error('Error deleting article: '. $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar articulo',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
