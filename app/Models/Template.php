<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TemplateCustom;
use App\Models\Document;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'type',
        'image_url',
        'is_default',
    ];

    /**
     * Get the template customs for this template.
     */
    public function templateCustoms()
    {
        return $this->hasMany(TemplateCustom::class);
    }

    /**
     * Get the documents using this template.
     */
    public function documents()
    {
        return $this->hasManyThrough(Document::class, TemplateCustom::class);
    }
}
