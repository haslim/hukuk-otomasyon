<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends BaseModel
{
    protected $table = 'document_versions';

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
