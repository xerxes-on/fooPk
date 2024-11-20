<?php

namespace App\Http\Controllers;

use App\Exceptions\NoData;
use App\Http\Requests\DiaryFormRequest;
use App\Services\DiaryService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

/**
 * Diary controller
 *
 * @package App\Http\Controllers
 */
final class DiaryController extends Controller
{
    /**
     * Form for creating a diary.
     */
    public function create(DiaryService $service): Factory|View
    {
        return view('diary.create', ['diary' => $service->getData()]);
    }

    /**
     * Create a diary.
     */
    public function store(DiaryFormRequest $request, DiaryService $service): Redirector|RedirectResponse
    {
        $service->processStore($request);
        return redirect('user/statistics')->with('success', trans('common.record_created_successfully'));
    }

    /**
     * Show statistics.
     */
    public function statistics(DiaryService $service): Factory|View
    {
        return view('diary.statistics', ['diaryData' => $service->getStatistics()]);
    }

    /**
     * Get data for a chart.
     */
    public function getChartData(Request $request, DiaryService $service): JsonResponse
    {
        return $service->getChartData($request);
    }

    /**
     * Diary edit form.
     */
    public function edit(Request $request, string $date, DiaryService $service): Factory|View
    {
        return view('diary.edit', ['diary' => $service->getStatistics($date), 'date' => $date]);
    }

    /**
     * Update a diary.
     */
    public function update(DiaryFormRequest $request, string|int $id, DiaryService $service): RedirectResponse
    {
        try {
            $service->processUpdate($request, $id);
            return redirect('user/statistics')->with('success', trans('common.record_updated_successfully'));
        } catch (NoData $e) {
            return redirect('user/statistics')->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a diary.
     */
    public function destroy(string|int $id, DiaryService $service): RedirectResponse
    {
        try {
            $service->processDestroy($id);
            return redirect('user/statistics')->with('success', trans('common.record_deleted'));
        } catch (NoData $e) {
            return redirect('user/statistics')->with('error', $e->getMessage());
        }
    }
}
