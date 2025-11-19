<?php

namespace Database\Seeders;

use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;

class WorkflowTemplateSeeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Dava Workflow',
                'case_type' => 'lawsuit',
                'tags' => ['dava', 'mahkeme'],
                'steps' => [
                    ['title' => 'Müvekkil Kaydı', 'is_required' => true],
                    ['title' => 'Dava Dilekçesi Hazırlığı', 'is_required' => true],
                    ['title' => 'Tebligat Kontrolü', 'is_required' => true],
                    ['title' => 'Ön İnceleme Duruşması', 'is_required' => false],
                    ['title' => 'Delil Bildirimi', 'is_required' => true],
                    ['title' => 'Esas Hakkında Beyan', 'is_required' => true],
                    ['title' => 'Karar Takibi', 'is_required' => true],
                ],
            ],
            [
                'name' => 'İcra Workflow',
                'case_type' => 'enforcement',
                'tags' => ['icra'],
                'steps' => [
                    ['title' => 'Takip Talebi', 'is_required' => true],
                    ['title' => 'Ödeme Emri Gönderimi', 'is_required' => true],
                    ['title' => 'İtiraz Kontrolü', 'is_required' => true],
                    ['title' => 'Haciz Talebi', 'is_required' => false],
                    ['title' => 'Satış İhalesi', 'is_required' => false],
                    ['title' => 'Tahsilat Planı', 'is_required' => true],
                ],
            ],
            [
                'name' => 'Arabuluculuk Workflow',
                'case_type' => 'mediation',
                'tags' => ['arabuluculuk'],
                'steps' => [
                    ['title' => 'Başvuru Değerlendirmesi', 'is_required' => true],
                    ['title' => 'Karşı Tarafa Davet', 'is_required' => true],
                    ['title' => 'İlk Toplantı', 'is_required' => true],
                    ['title' => 'Ara Oturumlar', 'is_required' => false],
                    ['title' => 'Anlaşma Taslağı', 'is_required' => true],
                    ['title' => 'Son Tutanak', 'is_required' => true],
                ],
            ],
        ];

        $hasOrderColumn = WorkflowStep::hasOrderColumn();

        foreach ($templates as $templateData) {
            $template = WorkflowTemplate::withTrashed()->firstOrNew([
                'case_type' => $templateData['case_type'],
                'name' => $templateData['name'],
            ]);

            $template->case_type = $templateData['case_type'];
            $template->name = $templateData['name'];
            $template->tags = $templateData['tags'];
            $template->deleted_at = null;
            $template->save();

            $template->steps()->delete();

            foreach ($templateData['steps'] as $index => $step) {
                $payload = [
                    'title' => $step['title'],
                    'is_required' => $step['is_required'],
                ];

                if ($hasOrderColumn) {
                    $payload['order'] = $index + 1;
                }

                $template->steps()->create($payload);
            }

            echo "✓ {$template->name} workflow hazırlandı" . PHP_EOL;
        }
    }
}
