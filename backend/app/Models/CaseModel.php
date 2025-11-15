<?php

namespace App\Models;

use App\Models\Client;

class CaseModel extends BaseModel
{
    protected $table = 'cases';

    protected $fillable = [
        'client_id',
        'case_no',
        'type',
        'title',
        'subject',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parties()
    {
        return $this->hasMany(CaseParty::class, 'case_id');
    }

    public function hearings()
    {
        return $this->hasMany(Hearing::class, 'case_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'case_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'case_id');
    }
}
