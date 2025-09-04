<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'user_id'     => $this->user_id,
            'images'      => $this->images->map(function ($image) {
                return [
                    'id'  => $image->id,
                    // ✅ Forzar URL absoluta completa incluso si el accessor no se aplica
                    'url' => $this->formatImageUrl($image->url),
                ];
                
            }),
        ];
    }

    private function formatImageUrl($url)
    {
        // Si ya es URL completa, regresarla tal cual
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        // Si comienza con /storage o storage, quitar el inicio
        $cleanPath = ltrim(preg_replace('#^/?storage/#', '', $url), '/');

        // Usar asset() para construir la URL completa basada en APP_URL
        return asset('storage/' . $cleanPath);
    }
}