<?php

namespace App\Http\Controllers\API;

use App\Exceptions\NoData;
use App\Http\Requests\DiaryFormRequest;
use App\Http\Resources\Diary\Diary as DiaryResource;
use App\Services\DiaryService;
use Illuminate\Http\{JsonResponse, Request};

/**
 * Controller to represent Diary functionality.
 *
 * @package App\Http\Controllers\API
 */
final class DiaryApiController extends APIBase
{
    /**
     * Get user diary statistics.
     *
     * @route GET /api/v1/diary/get
     */
    public function get(DiaryService $service): JsonResponse
    {
        $collection = DiaryResource::collection($service->getStatistics());
        return $collection->count() > 0 ?
            $this->sendResponse($collection, trans('common.success')) :
            $this->sendError(trans('common.no_data'));
    }

    /**
     * Store new diary data.
     *
     * @route POST /api/v1/diary/store
     */
    public function store(DiaryFormRequest $request, DiaryService $service): JsonResponse
    {
        if ($service->getData($request->created_at->toDateString())->count() === 0) {
            $service->processStore($request);
            return $this->sendResponse(true, trans('common.record_created_successfully'));
        }
        return $this->sendError(trans('common.existing_data'));
    }

    /**
     * Get data for a chart.
     *
     * @route POST /api/v1/diary/charts
     */
    public function getChartData(Request $request, DiaryService $service): JsonResponse
    {
        return $service->getChartData($request);
    }

    /**
     * Get data to edit.
     *
     * @route GET /api/v1/diary/edit/{date}
     */
    public function getForEdit(string $date, DiaryService $service): JsonResponse
    {
        $data = $service->getStatistics($date);
        if (is_null($data)) {
            return $this->sendError(trans('common.no_data'));
        }
        return $this->sendResponse(new DiaryResource($data), trans('common.success'));
    }

    /**
     * Update single diary entry.
     *
     * @route POST /api/v1/diary/update/{id}
     */
    public function update(DiaryFormRequest $request, int $id, DiaryService $service): JsonResponse
    {
        try {
            $service->processUpdate($request, $id);
            return $this->sendResponse(trans('common.record_updated_successfully'), trans('common.success'));
        } catch (NoData $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Destroy diary entry.
     *
     * @route DELETE /api/v1/diary/delete/{id}
     */
    public function destroy(int $id, DiaryService $service): JsonResponse
    {
        try {
            $service->processDestroy($id);
            return $this->sendResponse(trans('common.record_deleted'), trans('common.success'));
        } catch (NoData $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
