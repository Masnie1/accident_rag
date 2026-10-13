<?php

namespace App\Console\Commands;

use App\Models\Procedure;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\Ollama;

#[Signature('procedures:search {question}')]
#[Description('Affiche les 3 fiches les plus proches d\'une question')]
class SearchProcedures extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(Ollama $ollama)
    {
        // 1. Récupère la question entré dans le terminal
        $question = $this->argument('question');

        // 2. Calculer du vecteur de la question avec les fiches
        $vecteur = $ollama->embed($question)[0];

        // 3. Préparation de la requête
        $requete = Procedure::query()
                            ->select('code', 'titre')
                            ->selectVectorDistance('embedding', $vecteur, as: 'distance')
                            ->orderByVectorDistance('embedding', $vecteur)
                            ->limit(3); // récupère les 3 fiche les plus proche du vecteur

        // Affiche la requête SQL généré par Procedure en 3.
        // $this->line($requete->toSql());

        // 4. Exécute et affiche le résultat
        $resultats = $requete->get();

        $this->table(
            ['Code', 'Titre', 'Score'],
            $resultats->map(fn ($p) => [
                $p->code,
                $p->titre,
                round(1 - $p->distance, 3), // score = 1 - distance, arrondi à 3 décimales avec round()
            ])
        );

        return self::SUCCESS;
    }
}
