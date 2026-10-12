<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    protected $fillable = [
        'code', 'titre', 'service', 'gravite',
        'alertes', 'revision', 'contenu', 'embedding',
    ];

    protected function casts(): array
    {
        return [
            'alertes' => 'array',
            'revision' => 'date',
            'embedding' => 'array', // tableau PHP <-> format vector de pgvector
        ];
    }
}
