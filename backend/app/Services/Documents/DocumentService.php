<?php

namespace App\Services\Documents;

use App\Repositories\DocumentRepository;

class DocumentService
{
    public function __construct(private readonly DocumentRepository $documents)
    {
    }

    public function upload(string $caseId, array $metadata)
    {
        $document = $this->documents->create(array_merge($metadata, ['case_id' => $caseId]));
        $this->documents->addVersion($document->id, [
            'version' => 1,
            'path' => $metadata['path'] ?? 'storage/documents/' . uniqid() . '.pdf',
            'checksum' => $metadata['checksum'] ?? null
        ]);
        return $document->load('versions');
    }

    public function versions(string $documentId)
    {
        return $this->documents->find($documentId)->load('versions');
    }

    public function search(string $term)
    {
        return $this->documents->search($term);
    }
}
