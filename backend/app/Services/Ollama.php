<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Ollama
{
    private string $url;
    private string $embedModel;

    public function __construct()
    {
        $this->url = config('services.ollama.url');
        $this->embedModel = config("services.ollama.embed_model");
    }

    /**
     * Renvoie un vecteur par texte reçu, même quand on n'envoie qu'un seul texte.
     */
    public function embed(array|string $input): array
    {
        $vecteurs = Http::timeout(120) ->post($this->url.'/api/embed', [
                                        'model' => $this->embedModel,
                                        'input' => $input,
                                    ])->throw()->json('embeddings');
        return $vecteurs;
    }
}