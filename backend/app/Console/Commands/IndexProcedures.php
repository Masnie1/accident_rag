<?php

namespace App\Console\Commands;

use App\Models\Procedure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class IndexProcedures extends Command
{
    protected $signature = 'procedures:index';

    protected $description = 'Indexe les fiches d aides du dossier corpus dans pgvector';

    public function handle(): int
    {
        // 1. Lire les fiches d'aide au procédure (file en .md)
        $fichiers = File::glob(base_path('../corpus/*.md'));

        if (empty($fichiers)) {
            $this->error('Aucune fiche trouvée dans corpus/');
            return self::FAILURE;
        }

        $fiches = [];
        foreach ($fichiers as $chemin) {
            $doc = YamlFrontMatter::parse(File::get($chemin));
            $fiches[] = [
                'code' => $doc->matter('id'),
                'titre' => $doc->matter('titre'),
                'service' => $doc->matter('service'),
                'gravite' => $doc->matter('gravite'),
                'alertes' => $doc->matter('alertes'),
                'revision' => $doc->matter('revision'),
                'corps' => $doc->body(),
            ];
        }

        // 2. Calculer tous les vecteurs en un seul appel à Ollama model bg-m3
        $textes = array_map(fn (array $f) => $this->texteIndexe($f), $fiches);

        $vecteurs = Http::timeout(120)
            ->post(config('services.ollama.url').'/api/embed', [
                'model' => config('services.ollama.embed_model'),
                'input' => $textes,
            ])->throw()->json('embeddings');

        // 3. Enregistrement consistant à mettre à jour si le code existe déjà, sinon il est créer
        foreach ($fiches as $i => $f) {
            Procedure::updateOrCreate(
                ['code' => $f['code']],
                [
                    'titre' => $f['titre'],
                    'service' => $f['service'],
                    'gravite' => $f['gravite'],
                    'alertes' => $f['alertes'],
                    'revision' => $f['revision'],
                    'contenu' => $f['corps'],
                    'embedding' => $vecteurs[$i],
                ]
            );
            $this->line("{$f['code']}  {$f['titre']}");
        }

        $this->info(count($fiches).' fiches indexées.');

        return self::SUCCESS;
    }

    /**
     * Le texte transformé en vecteur servant à RECONNAITRE l'incident.
     * Titre + alertes + section Symptômes. Pas les commandes.
     */
    private function texteIndexe(array $f): string
    {
        preg_match('/^## Symptômes\s*\n(.*?)(?=^## |\z)/msu', $f['corps'], $m);
        return implode("\n", [$f['titre'], ...$f['alertes'], trim($m[1] ?? '')]);
    }
}