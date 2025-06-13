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
        'situation',
        'diplome',
        'experience',
        'soutien',
        'department',
        'motivation',
        'change_region',
        'project',
        'reason',
        'alone_team',
        'echeance',
        'employe',
        'employe_three',
        'investment_amount',
        'investment_share',
        'finance',
        'activity',
        'development_stage',
        'marketing_condition',
        'season',
        'eco_modal',
        'start_strategy',
        'market_qualification',
        'market_geography',
        'clients_identified',
        'client_knowledge',
        'competitors_identified',
        'suppliers_identified',
        'supplier_characteristics',
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
