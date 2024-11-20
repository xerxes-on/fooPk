<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NoData;
use App\Http\Requests\DiaryFormRequest;
use App\Http\Resources\Diary as DiaryResource;
use App\Models\DiaryData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\{JsonResponse, Request};
use Throwable;

/**
 * Class Diary.
 *
 * @package App\Services
 */
final class DiaryService
{
    /**
     * Retrieve chart specific data.
     *
     * @param string|null $date
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getData(?string $date = null): HasMany
    {
        if (is_string($date)) {
            $operator = 'LIKE';
            $value    = "$date%"; // We usually pass date not time, so this improves the search speed
        } else {
            $operator = '=';
            $value    = Carbon::today()->toDateString();
        }

        return \Auth::user()->diaryDates()->where('created_at', $operator, $value);
    }

    /**
     * Get statistics data for specific date or all available.
     *
     * @param string|null $date
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getStatistics(?string $date = null)
    {
        return is_null($date) ?
            \Auth::user()->diaryDates()->orderBy('created_at')->get() :
            \Auth::user()->diaryDates()->where('created_at', 'like', "%$date%")->first();
    }

    /**
     * Process data store.
     */
    public function processStore(DiaryFormRequest $request): void
    {
        $values               = $request->except('date');
        $values['created_at'] = $request->created_at->toDateTimeString();
        // remove the mood
        if ($request->has('mood') && (int)$request->get('mood') === 0) {
            $values = $request->except('mood');
        }

        $request->user()->diaryDates()->save(new DiaryData($values));
    }

    /**
     * Get data for a chart.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Carbon\Exceptions\InvalidFormatException
     * TODO: refactor required. separate data and only pass relevant data. Add Form Request validation
     */
    public function getChartData(Request $request): JsonResponse
    {
        $response = ['success' => false];

        if ($request->has('start') && $request->has('end')) {
            $from = Carbon::parse($request->get('start'))
                ->startOfDay()        // 2018-09-29 00:00:00.000000
                ->toDateTimeString(); // 2018-09-29 00:00:00

            $to = Carbon::parse($request->get('end'))
                ->endOfDay()          // 2018-09-29 23:59:59.000000
                ->toDateTimeString(); // 2018-09-29 23:59:59

            $diaryData = DiaryResource\Diary::collection(
                $request->user()
                    ->diaryDates()
                    ->whereBetween('created_at', [$from, $to])
                    ->orderBy('created_at')
                    ->get()
            );

            $response = ['success' => true, 'data' => $diaryData];
        }

        return response()->json($response);
    }

    /**
     * Process data update.
     *
     * @param DiaryFormRequest $request
     * @param string|int $id
     *
     * @throws NoData
     */
    public function processUpdate(DiaryFormRequest $request, string|int $id): void
    {
        try {
            $post   = DiaryData::findOrFail($id);
            $values = $request->validated();
            unset($values['created_at']);
            $post->update($values);
        } catch (Throwable) {
            throw new NoData(trans('common.nothing_found'));
        }
    }

    /**
     * Process data destroy.
     *
     * @param string|int $id
     *
     * @throws NoData
     */
    public function processDestroy(string|int $id): void
    {
        try {
            $post = DiaryData::findOrFail($id);
            $post->delete();
        } catch (Throwable) {
            throw new NoData(trans('common.nothing_found'));
        }
    }
}
