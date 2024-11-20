<?php

declare(strict_types=1);

namespace App\Admin\Http\Controllers;

use App\Enums\Admin\Permission\RoleEnum;
use App\Models\{Recipe, User};
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Course\Models\Course;
use Modules\Course\Service\WpApi;
use Yajra\DataTables\DataTables;

/**
 * Controller for all sorts of data.
 * TODO: Optimization required due to lots of duplicated queries
 * @package App\Http\Controllers\Admin
 */
final class DataTableAdminController extends Controller
{
    public function async(Request $request): ?JsonResponse
    {
        try {
            return match ($request->get('method')) {
                'recipesByUser'                    => $this->getRecipesByUser($request),
                'recipesByUserFromActiveChallenge' => $this->recipesByUserFromActiveChallenge($request),
                'allRecipes'                       => $this->allRecipes($request),
                'allUsers'                         => $this->allUsers($request),
                'courses'                          => $this->getWPArticles($request),
                default                            => null,
            };
        } catch (\Exception $e) {
            logError($e);
        }

        return null;
    }

    /**
     * get recipes by User
     *
     * @throws \Exception
     */
    public function getRecipesByUser(Request $request): JsonResponse
    {
        # get user by id
        $userId = (int)$request->get('userId');
        $user   = User::findOrFail($userId);

        // TODO:: review and refactor ..., trick to get valid recipes first @NickMost @AndreyNuritdinov
        $preferedUserRecipeCalculatedIds = DB::table('user_recipe_calculated')
            ->where('user_id', $user->id)
            ->orderBy('invalid', 'ASC')
            ->get(['id', 'recipe_id'])
            ->unique('recipe_id')
            ->pluck('id')
            ->toArray();
        // This hack is done to prevent showing recipes image that can be missing on local env
        $select = [
            'recipes.id',
            'recipes.cooking_time',
            'recipes.unit_of_time',
            'recipes.complexity_id',
            'recipes.status',
            'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
            'user_recipe_calculated.recipe_data AS calc_recipe_data',
            'user_recipe_calculated.invalid AS calc_invalid',
            'user_recipe_calculated.updated_at AS calc_updated_at',
            'favorites.user_id as favorite_user_id',
            'favorites.recipe_id as favorite_recipe_id',
        ];
        if (config('foodpunk.show_recipes_images') === true) {
            $select[] = 'recipes.image_file_name';
        }
        return DataTables::of(
            $user
                ->allRecipes()
                ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
                ->leftJoin('favorites', function ($join) use ($userId) {
                    $join->on('recipes.id', '=', 'favorites.recipe_id')->where('favorites.user_id', '=', $userId);
                })
                ->whereIn('user_recipe_calculated.id', $preferedUserRecipeCalculatedIds)
                ->select($select)
                ->where('user_recipe_calculated.user_id', $userId)
                ->groupBy('user_recipe_calculated.recipe_id')
        )
            ->only(
                [
                    'id',
                    '_image',
                    'title',
                    'proteins',
                    'fats',
                    'carbohydrates',
                    'calories',
                    '_cooking_time',
                    '_complexity',
                    '_mealTime',
                    'invalid',
                    'calculated',
                    '_diets',
                    'favorite',
                    'status'
                ]
            )
            ->filter(
                function (EloquentBuilder $query) use ($request): void {
                    $query->with(['ingestions:id', 'diets:id', 'complexity:id']);
                    // Check search filter, by id or name
                    $search = trim($request->get('search')['value'] ?? '');
                    if ($search !== '') {
                        if (is_numeric($search)) {
                            $query->where('recipes.id', 'like', "%{$search}%");
                            return;
                        }
                        $query->whereTranslationLike('title', "%$search%");
                    }
                }
            )
            ->order(function (EloquentBuilder $query) use ($request): void {
                if (!$request->has('order')) {
                    return;
                }
                $columns = [
                    1  => 'recipes.id',
                    4  => 'recipes.cooking_time',
                    5  => 'recipes.complexity_id',
                    6  => 'calc_ingestion_id',
                    7  => 'calc_invalid',
                    8  => 'calc_updated_at',
                    10 => 'recipes.status',
                    11 => 'favorite_recipe_id',
                ];
                $query->orderBy($columns[(int)$request->input('order.0.column')], $request->input('order.0.dir'));
            })
            ->addColumn(
                '_image',
                fn(Recipe $row): string => sprintf(
                    '<a href="%s" data-toggle="lightbox"><img class="thumbnail" src="%s" alt="" width="80px"></a>',
                    $row->image->url(),
                    $row->image->url('thumb')
                )
            )
            ->addColumn(
                '_cooking_time',
                fn(Recipe $row) => is_null($row->cooking_time) && is_null($row->unit_of_time) ?
                    '—' :
                    $row->cooking_time . ' ' . trans('common.' . $row->unit_of_time)
            )
            ->addColumn(
                '_complexity',
                fn(Recipe $row) => is_null($row->complexity_id) ? '—' : $row->complexity->title
            )
            ->addColumn(
                '_mealTime',
                function (Recipe $row): string {
                    $ingestions = $row->ingestions->pluck('title')->toArray();
                    return empty($ingestions) ? '' :
                        custom_implode($ingestions, '</span> <span class="badge badge-primary">', '<span class="badge badge-primary">', '</span>');
                }
            )
            ->addColumn(
                'invalid',
                function (Recipe $row) {
                    $html = ($row->calc_invalid) ? trans('common.Yes') : trans('common.No');
                    if (!empty($row->calc_recipe_data)) {
                        $calcRecipeData = json_decode((string)$row->calc_recipe_data, true);
                        if (!empty($calcRecipeData['notices'])) {
                            // by some strange occasion $calcRecipeData['notices'] can be a string
                            $html .= '<div>' . (
                                is_array($calcRecipeData['notices']) ?
                                    custom_implode($calcRecipeData['notices'], ' ') :
                                    $calcRecipeData['notices']
                            ) . '</div>';
                        }
                    }
                    return $html;
                }
            )
            ->addColumn(
                'calculated',
                function (Recipe $row): string {
                    $html = '';
                    if (!empty($row->calc_recipe_data)) {
                        $calcRecipeData = json_decode((string)$row->calc_recipe_data, true);
                        if (!empty($calcRecipeData['calculated_KCal'])) {
                            $html .= sprintf(
                                '<div>%s</div><div>%s %s</div>',
                                trans('common.recipe_calculated'),
                                trans('common.at'),
                                date('d.m.Y H:i:s', strtotime((string)$row->calc_updated_at))
                            );
                        }
                    }
                    return $html;
                }
            )
            ->addColumn(
                '_diets',
                function (Recipe $row): string {
                    $html = '';
                    foreach ($row->diets->pluck('name') as $diets) {
                        $html .= '<span class="badge badge-primary">' . $diets . '</span> ';
                    }
                    return $html;
                }
            )
            ->editColumn('status', fn(Recipe $row): ?string => trans('common.'.$row->status->lowerName()))
            ->addColumn(
                'favorite',
                fn(Recipe $row): string => $row->favorite_recipe_id === null ? '' : '<span class="fa fa-star text-success" aria-hidden="true"></span>'
            )
            ->rawColumns(['_image', '_mealTime', 'invalid', 'calculated', '_diets', 'favorite'])
            ->make();
    }

    /**
     * Get recipes by User from active Subscription
     *
     * @throws \Exception
     */
    public function recipesByUserFromActiveChallenge(Request $request): JsonResponse
    {
        # get user by id
        $userId = (int)$request->get('userId');
        $user   = User::with(['activeSubscriptions'])->whereId($userId)->firstOrFail();

        # check active challenge
        if (empty($user->subscription)) {
            return datatables()->collection([])->toJson();
        }

        $_query = $user->recipesByChallenge()
            ->with(/*'ingestions',*/ ['diets'])
            ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
            ->leftJoin('ingestions', 'user_recipe_calculated.ingestion_id', '=', 'ingestions.id')
            ->select(
                'recipes.*',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date',
                'ingestions.key AS meal_time'
            )
            ->whereColumn('user_recipe_calculated.ingestion_id', 'recipes_to_users.ingestion_id')
            ->where('user_recipe_calculated.user_id', $userId);

        return DataTables::of($_query)
            ->only(
                [
                    'id',
                    '_image',
                    'title',
                    'challenge_title',
                    'meal_date',
                    'meal_time',
                    'invalid',
                    '_kcal',
                    '_kh',
                    'calculated',
                    '_diets'
                ]
            )
            # from search
            ->filter(
                function (EloquentBuilder $query) use ($request): void {
                    if ($request->has('search') && trim($request->get('search')['value'] ?? '') != '') {
                        $value = $request->get('search')['value'];

                        $query->where('recipes.id', 'like', "%{$value}%");
                    }
                }
            )
            # column customization
            ->addColumn(
                '_image',
                fn(Recipe $row): string => sprintf(
                    '<a href="%s" data-toggle="lightbox"><img class="thumbnail" src="%s" alt="" width="80px"></a>',
                    $row->image->url(),
                    $row->image->url('thumb')
                )
            )
            ->addColumn(
                'challenge_title',
                fn(): string => '<span class="label label-info">Subscription (#' . $user->subscription->id . ')</span>'
            )
            ->editColumn(
                'meal_date',
                fn(Recipe $row): string => '<span class="label label-info">' . parseDateString(
                    $row->meal_date
                ) . '</span>'
            )
            ->editColumn(
                'meal_time',
                fn(Recipe $row): string => '<span class="label label-info">' . $row->meal_time . '</span>'
            )
            ->addColumn(
                'invalid',
                function (Recipe $row) {
                    $html = ($row->calc_invalid) ? trans('common.Yes') : trans('common.No');
                    if (!empty($row->calc_recipe_data)) {
                        $calcRecipeData = json_decode((string)$row->calc_recipe_data, true);
                        if (!empty($calcRecipeData['notices'])) {
                            // by some strange occasion $calcRecipeData['notices']) can be a string
                            $html .= '<div>' . (
                                is_array($calcRecipeData['notices']) ?
                                    custom_implode($calcRecipeData['notices'], ' ') :
                                    $calcRecipeData['notices']
                            ) . '</div>';
                        }
                    }
                    return $html;
                }
            )
            ->addColumn(
                '_kcal',
                function (Recipe $row): string {
                    $_kcal = trans('common.any');
                    if (!empty($row->min_kcal) && !empty($row->max_kcal)) {
                        $_kcal = "$row->min_kcal - $row->max_kcal";
                    } elseif (!empty($row->min_kcal)) {
                        $_kcal = "$row->min_kcal+";
                    } elseif (!empty($row->max_kcal)) {
                        $_kcal = "0 - $row->min_kcal";
                    }
                    return $_kcal;
                }
            )
            ->addColumn(
                '_kh',
                function (Recipe $row): string {
                    $_kh = trans('common.any');
                    if (!empty($row->min_kh) && !empty($row->max_kh)) {
                        $_kh = "$row->min_kh - $row->max_kh";
                    } elseif (!empty($row->min_kh)) {
                        $_kh = "$row->min_kh+";
                    } elseif (!empty($row->max_kcal)) {
                        $_kh = "0 - $row->max_kh";
                    }
                    return $_kh;
                }
            )
            ->addColumn(
                'calculated',
                function (Recipe $row): string {
                    $html = '';
                    if (!empty($row->calc_recipe_data)) {
                        $calcRecipeData = json_decode((string)$row->calc_recipe_data, true);
                        if (!empty($calcRecipeData['calculated_KCal'])) {
                            $html .= sprintf(
                                '<div>%s</div><div>%s %s</div>',
                                trans('common.recipe_calculated'),
                                trans('common.at'),
                                date('d.m.Y H:i:s', strtotime((string)$row->calc_updated_at))
                            );
                        }
                    }
                    return $html;
                }
            )
            ->addColumn(
                '_diets',
                function (Recipe $row): string {
                    $html = '';
                    if ($row->diets->count()) {
                        foreach ($row->diets as $diet) {
                            $html .= '<span class="label label-info">' . $diet->name . '</span> ';
                        }
                    }
                    return $html;
                }
            )
            ->rawColumns(['_image', 'challenge_title', 'meal_date', 'meal_time', 'invalid', 'calculated', '_diets'])
            ->make();
    }

    /**
     * get all recipes
     *
     * @throws \Exception
     */
    public function allRecipes(Request $request): JsonResponse
    {
        return DataTables::of(Recipe::isActive()
            ->with(
                [
                    'ingestions',
                    'diets',
                    'complexity',
                    'publicTags.translations',
                    'price'
                ]
            ))
            ->filter(function (EloquentBuilder $query) use ($request): void { // manual search to make it work fine
                // Detect if only user recipes should be included
                if ($request->has('userId')) {
                    $query->whereNotIn(
                        'recipes.id',
                        fn(Builder $query): Builder => $query->select('recipe_id')
                            ->distinct()
                            ->from('user_recipe')
                            ->where('user_id', (int)$request->get('userId'))
                    );
                }

                // Check search filter, by id or name
                $search = trim($request->get('search')['value'] ?? '');
                if ($search !== '') {
                    if (is_numeric($search)) {
                        $query->where('recipes.id', 'like', "%{$search}%");
                        return;
                    }
                    $query->whereTranslationLike('title', "%$search%");
                }

                // Check if filters should be applied
                $filters = $request->get('filters');
                if (is_null($filters)) {
                    return;
                }
                // apply filters
                $query->when($filters['ingestion'], function (EloquentBuilder $query, int $value) {
                    return $query->whereHas('ingestions', fn(EloquentBuilder $query) => $query->where('ingestion_id', $value));
                });

                $query->when($filters['diet'], function (EloquentBuilder $query, int $value) {
                    return $query->whereHas('diets', fn(EloquentBuilder $query) => $query->where('diet_id', $value));
                });

                $query->when($filters['complexity'], function (EloquentBuilder $query, int $value) {
                    $query->where('complexity_id', $value);
                });

                $query->when($filters['cost'], function (EloquentBuilder $query, int $value) {
                    $query->where('price_id', $value);
                });

                $query->when($filters['tag'], function (EloquentBuilder $query, int $value) {
                    return $query->whereHas('publicTags', fn(EloquentBuilder $query) => $query->where('recipe_tag_id', $value));
                });
            })
            ->only(['id', 'title', 'ingestions', 'diets', 'status', 'complexity', 'price', 'public_tags'])
            ->addColumn(
                'title',
                fn(Recipe $row): string => $row->title ?? ''
            )
            ->editColumn(
                'ingestions',
                fn(Recipe $row): string => custom_implode($row->ingestions->pluck('title')->toArray())
            )
            ->editColumn(
                'diets',
                fn(Recipe $row): string => custom_implode($row->diets->pluck('name')->toArray())
            )
            ->editColumn(
                'status',
                fn(Recipe $row): string => trans('common.'.$row->status->lowerName())
            )
            ->editColumn(
                'complexity',
                fn(Recipe $row): string => $row->complexity->title ?? ''
            )
            ->editColumn(
                'price',
                fn(Recipe $row): ?string => $row->price->title ?? ''
            )
            ->editColumn(
                'public_tags',
                fn(Recipe $row): ?string => custom_implode($row->publicTags->pluck('title')->toArray())
            )->make();
    }

    /**
     * Get all or filtered Users.
     * @throws \Exception
     */
    public function allUsers(Request $request): JsonResponse
    {
        return DataTables::of(User::query())
            ->filter(
                function (EloquentBuilder $query) use ($request): void {
                    $admin = $request->user();
                    // Limit users to those the admin is liable for
                    if ($admin->hasRole(RoleEnum::CONSULTANT->value)) {
                        $query->whereIn('users.id', $admin->liableClients->pluck('id')); // TODO: optimize
                    }

                    if ($request->has('filter')) {
                        $query->searchBy($request->get('filter'));
                    }

                    $query->withCount('activeSubscriptions')
                        ->with([
                            'latestQuestionnaireRelation' => fn(HasOne $formular) => $formular->withCount('answers'),
                        ]);
                }
            )
            ->order(function (EloquentBuilder $query) use ($request): void {
                if (!$request->has('order')) {
                    return;
                }
                $columns = [
                    0 => 'id',
                    1 => 'first_name',
                    2 => 'last_name',
                    3 => 'email',
                    7 => 'lang',
                    8 => 'created_at'
                ];
                $order = $columns[$request->input('order.0.column')];
                $dir   = $request->input('order.0.dir');
                $query->orderBy("users.$order", $dir);
            })
            ->addColumn(
                'formular_approved',
                function (User $row): string {
                    // relation is already loaded, we just obtain from collection
                    $questionnaire = $row->latestQuestionnaireRelation;

                    if (is_null($questionnaire)) {
                        return trans('admin.filters.formular.options.missing');
                    }
                    if (!$questionnaire) {
                        return trans('admin.filters.formular.options.not_approved');
                    }
                    if ($questionnaire?->answers_count <= 0) {
                        return trans('admin.filters.formular.options.not_approved');
                    }
                    if (!$questionnaire->is_approved) {
                        return trans('admin.filters.formular.options.not_approved');
                    }
                    return trans('admin.filters.formular.options.approved');
                }
            )
            ->addColumn(
                'subscription',
                fn(User $row): string => $row->active_subscriptions_count ? trans('common.yes') : trans('common.no')
            )
            ->editColumn(
                'status',
                fn(User $row): string => $row->status ? trans('common.yes') : trans('common.no')
            )
            ->editColumn(
                'lang',
                fn(User $row): string => trans("admin.filters.language.$row->lang")
            )
            ->editColumn(
                'created_at',
                fn(User $row): ?\Illuminate\Support\Carbon => $row->created_at
            )
            ->make();
    }

    /**
     * get WordPress Articles
     *
     * @throws \Exception
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getWPArticles(Request $request): JsonResponse
    {
        # get challenge by ID
        $aboChallengeId = (int)$request->get('aboChallengeId');
        $aboChallenge   = Course::findOrFail($aboChallengeId);

        # get article IDs
        $articleIDs = $aboChallenge->articles()->pluck('wp_article_id', 'days')->toArray();

        # get articles content by IDs
        $articleContent = empty($articleIDs) ? $articleIDs : WpApi::getPosts(array_values($articleIDs));

        return DataTables::of($aboChallenge->articles())
            ->addColumn(
                'article_title',
                fn($row): ?string => isset($articleContent[$row->wp_article_id]) ?
                    html_entity_decode((string)$articleContent[$row->wp_article_id]['post_title']) :
                    null
            )
            ->make();
    }
}
