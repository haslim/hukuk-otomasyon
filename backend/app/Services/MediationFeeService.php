<?php

namespace App\Services;

use App\Models\MediationFeeCalculation;
use App\Models\MediationFeeTariff;
use App\Repositories\BaseRepository;

class MediationFeeService
{
    private BaseRepository $repository;
    private array $standardTariffs;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
        $this->repository->setModel(new MediationFeeCalculation());
        $this->initializeStandardTariffs();
    }

    private function initializeStandardTariffs()
    {
        // 6325 sayılı Hukuk Uyuşmazlıklarında Arabuluculuk Kanunu'na göre tarifeler
        $this->standardTariffs = [
            'standard' => [
                ['min' => 0, 'max' => 5000, 'fee' => 650, 'party_rule' => 'total'],
                ['min' => 5000.01, 'max' => 10000, 'fee' => 950, 'party_rule' => 'total'],
                ['min' => 10000.01, 'max' => 25000, 'fee' => 1650, 'party_rule' => 'total'],
                ['min' => 25000.01, 'max' => 50000, 'fee' => 2330, 'party_rule' => 'total'],
                ['min' => 50000.01, 'max' => 100000, 'fee' => 2960, 'party_rule' => 'total'],
                ['min' => 100000.01, 'max' => 250000, 'fee' => 3610, 'party_rule' => 'total'],
                ['min' => 250000.01, 'max' => 500000, 'fee' => 4840, 'party_rule' => 'total'],
                ['min' => 500000.01, 'max' => 1000000, 'fee' => 6070, 'party_rule' => 'total'],
                ['min' => 1000000.01, 'max' => 2500000, 'fee' => 7300, 'party_rule' => 'total'],
                ['min' => 2500000.01, 'max' => 5000000, 'fee' => 9750, 'party_rule' => 'total'],
                ['min' => 5000000.01, 'max' => 10000000, 'fee' => 12190, 'party_rule' => 'total'],
                ['min' => 10000000.01, 'max' => null, 'fee' => 14630, 'party_rule' => 'total']
            ],
            'commercial' => [
                ['min' => 0, 'max' => 50000, 'fee' => 2960, 'party_rule' => 'total'],
                ['min' => 50000.01, 'max' => 100000, 'fee' => 3610, 'party_rule' => 'total'],
                ['min' => 100000.01, 'max' => 250000, 'fee' => 4840, 'party_rule' => 'total'],
                ['min' => 250000.01, 'max' => 500000, 'fee' => 6070, 'party_rule' => 'total'],
                ['min' => 500000.01, 'max' => 1000000, 'fee' => 7300, 'party_rule' => 'total'],
                ['min' => 1000000.01, 'max' => 2500000, 'fee' => 9750, 'party_rule' => 'total'],
                ['min' => 2500000.01, 'max' => 5000000, 'fee' => 12190, 'party_rule' => 'total'],
                ['min' => 5000000.01, 'max' => 10000000, 'fee' => 14630, 'party_rule' => 'total'],
                ['min' => 10000000.01, 'max' => 25000000, 'fee' => 19500, 'party_rule' => 'total'],
                ['min' => 25000000.01, 'max' => null, 'fee' => 24370, 'party_rule' => 'total']
            ],
            'urgent' => [
                ['min' => 0, 'max' => null, 'percentage' => 2.0, 'party_rule' => 'per_party']
            ]
        ];
    }

    public function calculateFee(array $data): array
    {
        $calculationType = $data['calculation_type'] ?? 'standard';
        $subjectValue = (float) ($data['subject_value'] ?? 0);
        $partyCount = (int) ($data['party_count'] ?? 2);
        $vatRate = (float) ($data['vat_rate'] ?? 18);

        $tariffs = $this->standardTariffs[$calculationType] ?? $this->standardTariffs['standard'];
        $applicableTariff = $this->findApplicableTariff($tariffs, $subjectValue);

        if (!$applicableTariff) {
            throw new \Exception('Bu değer için uygun tarife bulunamadı');
        }

        $baseFee = $this->calculateBaseFee($applicableTariff, $subjectValue);
        $vatAmount = $baseFee * ($vatRate / 100);
        $totalFee = $baseFee + $vatAmount;
        $feePerParty = $applicableTariff['party_rule'] === 'per_party' ? $totalFee : $totalFee / $partyCount;

        return [
            'calculation_type' => $calculationType,
            'party_count' => $partyCount,
            'subject_value' => $subjectValue,
            'base_fee' => $baseFee,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total_fee' => $totalFee,
            'fee_per_party' => $feePerParty,
            'calculation_details' => [
                'applicable_tariff' => $applicableTariff,
                'calculation_steps' => $this->getCalculationSteps($applicableTariff, $subjectValue, $baseFee, $vatRate, $vatAmount, $totalFee)
            ]
        ];
    }

    private function findApplicableTariff(array $tariffs, float $value): ?array
    {
        foreach ($tariffs as $tariff) {
            if ($value >= $tariff['min'] && ($tariff['max'] === null || $value <= $tariff['max'])) {
                return $tariff;
            }
        }
        return null;
    }

    private function calculateBaseFee(array $tariff, float $subjectValue): float
    {
        if (isset($tariff['fee'])) {
            return (float) $tariff['fee'];
        }

        if (isset($tariff['percentage'])) {
            return $subjectValue * ($tariff['percentage'] / 100);
        }

        return 0;
    }

    private function getCalculationSteps(array $tariff, float $subjectValue, float $baseFee, float $vatRate, float $vatAmount, float $totalFee): array
    {
        $steps = [];

        if (isset($tariff['min']) && isset($tariff['max'])) {
            $steps[] = "Değerleme konusu tutar: " . number_format($subjectValue, 2, ',', '.') . " ₺";
            $steps[] = "Tarife aralığı: " . number_format($tariff['min'], 2, ',', '.') . " ₺ - " . 
                       ($tariff['max'] ? number_format($tariff['max'], 2, ',', '.') . " ₺" : "Sınırsız");
        }

        if (isset($tariff['fee'])) {
            $steps[] = "Tarife ücreti: " . number_format($tariff['fee'], 2, ',', '.') . " ₺";
        } elseif (isset($tariff['percentage'])) {
            $steps[] = "Yüzde oranı: %" . number_format($tariff['percentage'], 2, ',', '.');
            $steps[] = "Hesaplama: " . number_format($subjectValue, 2, ',', '.') . " × %" . number_format($tariff['percentage'], 2, ',', '.') . " = " . number_format($baseFee, 2, ',', '.') . " ₺";
        }

        $steps[] = "KDV (%{$vatRate}): " . number_format($vatAmount, 2, ',', '.') . " ₺";
        $steps[] = "Toplam ücret: " . number_format($totalFee, 2, ',', '.') . " ₺";

        return $steps;
    }

    public function storeCalculation(array $data): MediationFeeCalculation
    {
        $calculationData = $this->calculateFee($data);
        
        $calculationData['case_id'] = $data['case_id'] ?? null;
        $calculationData['client_id'] = $data['client_id'] ?? null;
        $calculationData['calculation_date'] = now()->toDateString();
        $calculationData['created_by'] = $data['created_by'] ?? null;

        return $this->repository->create($calculationData);
    }

    public function getCalculations(array $filters = []): array
    {
        $query = $this->repository->newQuery();

        if (isset($filters['case_id'])) {
            $query->where('case_id', $filters['case_id']);
        }

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['calculation_type'])) {
            $query->where('calculation_type', $filters['calculation_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('calculation_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('calculation_date', '<=', $filters['date_to']);
        }

        return $query->with(['case', 'client', 'creator'])
                     ->orderBy('calculation_date', 'desc')
                     ->get()
                     ->toArray();
    }

    public function getCalculationById(string $id): ?MediationFeeCalculation
    {
        return $this->repository->findById($id);
    }

    public function deleteCalculation(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getTariffSummary(): array
    {
        return [
            'standard' => $this->standardTariffs['standard'],
            'commercial' => $this->standardTariffs['commercial'],
            'urgent' => $this->standardTariffs['urgent']
        ];
    }

    public function validateCalculationData(array $data): array
    {
        $errors = [];

        if (!isset($data['subject_value']) || $data['subject_value'] <= 0) {
            $errors['subject_value'] = 'Değerleme konusu tutar 0\'dan büyük olmalıdır';
        }

        if (!isset($data['party_count']) || $data['party_count'] < 1) {
            $errors['party_count'] = 'Taraf sayısı 1 veya daha fazla olmalıdır';
        }

        if (isset($data['vat_rate']) && ($data['vat_rate'] < 0 || $data['vat_rate'] > 100)) {
            $errors['vat_rate'] = 'KDV oranı 0-100 arasında olmalıdır';
        }

        if (isset($data['calculation_type']) && !in_array($data['calculation_type'], ['standard', 'commercial', 'urgent'])) {
            $errors['calculation_type'] = 'Geçersiz hesaplama türü';
        }

        return $errors;
    }
}
