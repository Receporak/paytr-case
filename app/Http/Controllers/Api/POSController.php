<?php

namespace App\Http\Controllers\Api;

use App\DTOs\POS\POSSelectionDTO;
use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Jobs\SyncPosRatesJob;
use App\Services\POS\POSSelectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
{
    public function select(Request $request, POSSelectionService $posSelectionService): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'currency' => ['required', 'string', 'in:TRY,USD,EUR'],
            'card_type' => ['required', 'string', 'in:credit,debit,unknown'],
            'installment' => ['required', 'integer', 'min:1'],
        ]);

        if ($validated->fails()) {
            return response()->json(BaseResponse::error($validated->errors()->first())->toArray());
        }
        $posData = POSSelectionDTO::fromArray($validated->validated());
        $posRateService = $posSelectionService->selectLowest($posData);

        $response = [
            'filters' => [
                'installment' => $posData->installment,
                'currency' => $posData->currency,
                'card_type' => $posData->cardType,
                'card_brand' => $posRateService->cardBrand,
            ],
            'overall_min' => $posRateService->toArray(),
        ];

        return response()->json(BaseResponse::success($response)->toArray());
    }

    public function rateSync(): JsonResponse
    {
        SyncPosRatesJob::dispatch();
        return response()->json(BaseResponse::success("Senkronizasyon kuyruğa alındı")->toArray());
    }
}
