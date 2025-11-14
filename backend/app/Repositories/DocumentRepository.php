<?php

namespace App\Repositories;

use App\Models\Document;
use App\Models\DocumentVersion;

class DocumentRepository extends BaseRepository
{
    public function __construct(Document $model)
    {
        parent::__construct($model);
    }

    public function addVersion(string $documentId, array $data)
    {
        $version = new DocumentVersion($data);
        $version->document_id = $documentId;
        $version->save();
        return $version;
    }

    public function search(string $term)
    {
        return $this->model->newQuery()
            ->whereFullText(['title', 'content'], $term)
            ->with('versions')
            ->paginate(25);
    }
}
