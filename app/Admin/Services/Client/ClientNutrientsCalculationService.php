<?php

declare(strict_types=1);

namespace App\Admin\Services\Client;

use App\Enums\Admin\Permission\RoleEnum;
use App\Models\User;
use Calculation;

/**
 * Service to handle client nutrients calculations.
 *
 * @used-by \App\Admin\Http\Controllers\ClientsAdminController::calculations()
 * @package App\Services\Admin\Calculations
 */
final class ClientNutrientsCalculationService
{
    public function resetCalculation(array $data): void
    {
        $clientData = Calculation::prepareUserDataForCalculations($data['client_id']);
        unset($clientData['dietdata']);
        $this->storeClientData($data, $clientData);
    }

    private function storeClientData(array $data, array $clientData = null): void
    {
        if (!$data['user']->isQuestionnaireExist()) {
            return;
        }
        $dietData = Calculation::calcUserNutrients($data['client_id'], $clientData);
        if (!$dietData) {
            return;
        }
        User::updateOrCreate(['id' => $data['client_id']], ['dietdata' => $dietData])->syncRoles(RoleEnum::USER->value);
    }

    public function recalculate(array $data): void
    {
        $client = $this->getCalculationData($data);

        $tmp = $data['ingestion'];
        if ($tmp && is_array($tmp)) {
            foreach ($tmp as $ingestionType => $values) {
                $client['predefined_values']['ingestion'][$ingestionType]['percents'] = $values['percents'];
            }
        }
        // Override users diet data

        $this->storeClientData($data, $client);
    }

    private function getCalculationData(array $data): array
    {
        $client = Calculation::prepareUserDataForCalculations($data['client_id']);

        $client['Kcal']        = $client['predefined_values']['Kcal'] = $data['Kcal'];
        $client['KH']          = $client['predefined_values']['KH'] = $data['KH'];
        $client['ew_percents'] = $client['predefined_values']['ew_percents'] = $data['ew_percents'];

        return $client;
    }

    public function saveCustomNutrients(array $data): void
    {
        // save_custom_nutrients
        $client = $this->getCalculationData($data);
        $tmp    = $data['ingestion'];
        if ($tmp && (is_array($tmp))) {
            foreach ($tmp as $ingestionType => $values) {
                $client['predefined_values']['ingestion'][$ingestionType]['percents'] = $values['percents'];
                $client['ingestion'][$ingestionType]                                  = $values;
            }
        }
        if (!$data['user']->isQuestionnaireExist()) {
            return;
        }
        $tmp = Calculation::calcUserNutrients($data['client_id'], $client);
        if (!$tmp) {
            return;
        }
        $tmp['ingestion'] = $client['ingestion'];
        User::updateOrCreate(['id' => $data['client_id']], ['dietdata' => $tmp])->syncRoles(RoleEnum::USER->value);
    }
}
