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
        // Shared hosting ortaminda FULLTEXT indeksleri veya destekli engine
        // olmayabilecegi icin, burada whereFullText yerine LIKE tabanli
        // daha genis uyumlu bir arama kullaniliyor.
        return $this->model->newQuery()
            ->where(function ($query) use ($term) {
                $like = '%' . $term . '%';
                $query->where('title', 'LIKE', $like)
                    ->orWhere('content', 'LIKE', $like);
            })
            ->with('versions')
            ->limit(25)
            ->get();
    }
}
