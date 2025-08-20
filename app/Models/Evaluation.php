<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'age',
        'situationPro',
        'activiteCorrespond',
        'experiencePro',
        'soutienEntourage',
        'departement',
        'critereChoix',
        'mobilite',
        'projetDescription',
        'projetRaison',
        'projetEquipe',
        'projetEcheance',
        'emploisDemarrage',
        'emploisTroisAns',
        'montantInvestissements',
        'apportPersonnelPourcentage',
        'projetFinancement',
        'produitSecteurActivite',
        'produitStadeDeveloppement',
        'produitConditionCommercialisation',
        'produitSaisonnalite',
        'produitModeleEconomique',
        'produitStrategieDemarrage',
        'marcheQualification',
        'marcheDimensionGeographique',
        'marcheFutursClientsIdentification',
        'marcheFutursClientsConnaissance',
        'marcheConcurrentsIdentification',
        'marcheFournisseursIdentification',
        'marcheFournisseursCaracteristiques',
        'ai_response',
    ];
    
    protected $casts = [
        'ai_response' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
