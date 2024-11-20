<?php

namespace App\Http\Controllers\API;

use App\Exceptions\PublicException;
use App\Http\Resources\{AllergyTypes, SurveyAnswer as SurveyAnswerResource, SurveyQuestion};
use App\Models\SurveyAnswer;
use App\Repositories\Formular as FormularRepository;
use App\Services\FormularService;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Formular API controller.
 *
 * TODO: refactor repo into service injection
 * @deprecated
 * @package App\Http\Controllers\API
 */
final class FormularApiController extends APIBase
{
    /**
     * Formular constructor.
     */
    public function __construct(private readonly FormularRepository $formularRepo)
    {
    }

    /**
     * Buy formular edit permission.
     *
     * @route GET /api/v1/formular/buy-edit
     */
    public function buyEditing(Request $request): JsonResponse
    {
        try {
            if (false === $this->formularRepo->processBuyEditing($request->user())) {
                return $this->getFormular($request);
            }
        } catch (PublicException $e) {
            return $this->sendError(message: $e->getMessage(), status: ResponseAlias::HTTP_METHOD_NOT_ALLOWED);
        } catch (InsufficientFunds $e) {
            return $this->sendError(message: $e->getMessage(), status: ResponseAlias::HTTP_PAYMENT_REQUIRED);
        } catch (ExceptionInterface) {
            return $this->sendError(message: trans('api.withdraw_fail'), status: ResponseAlias::HTTP_PAYMENT_REQUIRED);
        }

        return $this->sendResponse(
            $this->getFormular($request)->getData(true)['data'],
            trans('api.amount_foodpoints_withdraw', ['amount' => config('formular.formular_editing_price_foodpoints')])
        );
    }

    /**
     * Get filled formular with answers.
     *
     * @route GET /api/v1/formular/get
     */
    public function getFormular(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isFormularExist()) {
            return $this->sendError(message: trans('api.formular_404'));
        }

        $answeredQuestions = $user->formular->answers->sortBy('question.order');

        // daily_routine question should be excluded from editing or displaying
        $answeredQuestions = $answeredQuestions->filter(
            fn(SurveyAnswer $item) => $item->question->key_code !== 'daily_routine'
        );

        return $this->sendResponse(
            [
                'questions'     => SurveyAnswerResource::collection($answeredQuestions),
                'allergy-types' => AllergyTypes::collection(\App\Models\AllergyTypes::with('allergies')->get()),
            ],
            trans('common.success')
        );
    }

    /**
     * Get formular questions.
     *
     * @route GET /api/v1/formular/questions
     */
    public function getQuestions(): JsonResponse
    {
        return $this->sendResponse(
            [
                'questions'     => SurveyQuestion::collection($this->formularRepo->getSurveyQuestion()),
                'allergy-types' => AllergyTypes::collection(\App\Models\AllergyTypes::with('allergies')->get()),
            ],
            trans('common.success')
        );
    }

    /**
     * Store data from formular.
     *
     * @route POST /api/v1/formular/store
     */
    public function store(Request $request): JsonResponse
    {
        // TODO: Should be refactored to correctly pass data
        try {
            $this->formularRepo->processStore($request);
        } catch (Throwable $e) {
            return $this->sendError($e->getMessage(), $e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        return $this->sendResponse(true, trans('common.formular_user_updated'));
    }

    /**
     * Retrieve user formular status
     *
     * @route GET /api/v1/formular/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user     = $request->user();
        $formular = $user->formular; // Introduced to decrease query duplication by 20!!
        return $this->sendResponse(
            [
                'is_exists'          => !empty($formular) && $formular?->answers?->count() > 0,
                'is_approved'        => (bool)$formular?->approved,
                'available_for_edit' => $user->canEditFormular(),
            ],
            trans('common.success')
        );
    }

    /**
     * Check when user can edit ones formular for free
     *
     * @route GET /api/v1/formular/check-free-edit
     */
    public function checkEditPeriod(Request $request, FormularService $service): JsonResponse
    {
        $canEdit = $request->user()->canEditFormular();
        return $this->sendResponse(
            [
                'can_edit' => $canEdit,
                'title'    => $canEdit ? trans('api.formular.edit_check_free.title') : trans('mobile_app.allert_to_change_data'),
                'body'     => $canEdit ?
                    trans('api.formular.edit_check_free.body') :
                    trans(
                        'common.free_change_formular_in_days',
                        [
                            'amount' => config('formular.formular_editing_price_foodpoints'),
                            'number' => $service->getFreeEditPeriod($request->user()),
                        ],
                    ),
                'button' => $canEdit ?
                    trans('api.formular.edit_check_free.button') :
                    trans('api.formular.edit_check_paid.button')
            ],
            ''
        );
    }

    /**
     * Clear formular with answers.
     *
     * Used only in postman for testing purposes only! Not for production!
     *
     * @route DELETE /api/v1/formular/delete
     */
    public function clearUserFormular(Request $request): JsonResponse
    {
        if ('production' === config('app.env')) {
            return $this->sendError(message: 'Not allowed', status: ResponseAlias::HTTP_METHOD_NOT_ALLOWED);
        }

        $user             = $request->user();
        $answers_deleted  = SurveyAnswer::whereUserId($user->id)->delete();
        $formular_deleted = $user->formulars()->delete();
        $user->forgetFormularCache();
        return $this->sendResponse(
            [
                'answers_deleted'  => $answers_deleted,
                'formular_deleted' => $formular_deleted,
            ],
            trans('common.success')
        );
    }
}
