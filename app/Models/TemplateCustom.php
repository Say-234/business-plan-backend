<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Document;
use App\Models\Template;

class TemplateCustom extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'template_id',
        'style',
    ];

    protected $casts = [
        'style' => 'array',
    ];

    /**
     * Get the document that owns this template custom.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the template that owns this template custom.
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
