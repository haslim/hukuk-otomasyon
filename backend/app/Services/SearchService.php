<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Document;

class SearchService
{
    public function global(string $term): array
    {
        return [
            'clients' => Client::whereFullText(['name', 'identifier'], $term)->limit(10)->get(),
            'cases' => CaseModel::whereFullText(['title', 'case_no'], $term)->limit(10)->get(),
            'documents' => Document::whereFullText(['title', 'content'], $term)->limit(10)->get()
        ];
    }
}
