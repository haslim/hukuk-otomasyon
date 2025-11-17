<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\Client;
use App\Models\Document;

class SearchService
{
    public function global(string $term): array
    {
        $like = '%' . $term . '%';

        return [
            'clients' => Client::where(function ($query) use ($like) {
                $query->where('name', 'LIKE', $like)
                    ->orWhere('identifier', 'LIKE', $like);
            })->limit(10)->get(),
            'cases' => CaseModel::where(function ($query) use ($like) {
                $query->where('title', 'LIKE', $like)
                    ->orWhere('case_no', 'LIKE', $like);
            })->limit(10)->get(),
            'documents' => Document::where(function ($query) use ($like) {
                $query->where('title', 'LIKE', $like)
                    ->orWhere('content', 'LIKE', $like);
            })->limit(10)->get(),
        ];
    }
}
