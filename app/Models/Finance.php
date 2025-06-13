<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finance extends Model
{
    protected $fillable = [
        'document_id',
        'produits',
        'vente_previsionnels',
        'capital_demarrage',
        'emprunts',
        'personnel',
        'investissements',
        'frais_fonctionnement'
    ];

    protected $casts = [
        'produits' => 'array',
        'vente_previsionnels' => 'array',
        'capital_demarrage' => 'array',
        'emprunts' => 'array',
        'personnel' => 'array',
        'investissements' => 'array',
        'frais_fonctionnement' => 'array'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Formatage des données produit avant sauvegarde
     */
    public function setProduitsAttribute($value)
    {
        $this->attributes['produits'] = $this->formatProduitData($value);
    }

    protected function formatProduitData($data)
    {
        if (is_array($data)) {
            // Calcul des sous-totaux automatiques si nécessaire
            if (isset($data['matieres_premieres'])) {
                foreach ($data['matieres_premieres'] as &$matiere) {
                    if (isset($matiere['quantite']) && isset($matiere['cout_unitaire'])) {
                        $matiere['sous_total'] = $matiere['quantite'] * $matiere['cout_unitaire'];
                    }
                }
            }
            
            if (isset($data['main_doeuvre_directe'])) {
                foreach ($data['main_doeuvre_directe'] as &$labour) {
                    if (isset($labour['quantite']) && isset($labour['cout_unitaire'])) {
                        $labour['sous_total'] = $labour['quantite'] * $labour['cout_unitaire'];
                    }
                }
            }
            
            if (isset($data['couts_indirects'])) {
                foreach ($data['couts_indirects'] as &$cost) {
                    if (isset($cost['quantite']) && isset($cost['cout_unitaire'])) {
                        $cost['sous_total'] = $cost['quantite'] * $cost['cout_unitaire'];
                    }
                }
            }
            
            return json_encode($data);
        }
        
        return $data;
    }
}
