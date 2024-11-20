<?php

namespace App\Http\Controllers;

use App\Exceptions\PublicException;
use App\Http\Requests\FormularFormRequest as Request;
use App\Models\AllergyTypes;
use App\Models\User;
use App\Repositories\Formular as FormularRepository;
use App\Services\FormularService;
use Auth;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Formular controller
 * @deprecated
 * @package App\Http\Controllers
 */
final class FormularController extends Controller
{
    /**
     * Formular constructor.
     *
     * @param \App\Repositories\Formular $formularRepo
     */
    public function __construct(private FormularRepository $formularRepo)
    {
    }

    /**
     * Show questions for authenticated user
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(): Factory|View|RedirectResponse
    {
        $user = auth()->user();
        if ($user->isFormularExist() && !$user->canEditFormular()) {
            return redirect()->route('recipes.list')->with('warning', trans('common.formular_already_exist'));
        }

        $allergyTypes = AllergyTypes::with('allergies')->get();
        $questions    = $this->formularRepo->getFormularQuestions($this->formularRepo->getSurveyQuestion());

        return view('formular.create', compact('questions', 'allergyTypes'));
    }

    /**
     * Submit answers, probably
     *
     * @param \App\Http\Requests\FormularFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->formularRepo->processStore($request);
        } catch (\Throwable $e) {
            logError($e);
        }

        if (!Auth::check()) {
            return redirect()->to(config('formular.redirect_link'));
            // Nick Most 202102013 disabled redirection after formular to external link
            //            return redirect()->route('formular.pricingTable')
            //                ->with('email', $_user->email)
            //                ->with('firstname', $_user->first_name)
            //                ->with('lastname', $_user->last_name);
        }

        return redirect('user/recipes/list')
            ->with('success', trans('common.formular_user_updated'));
    }

    /**
     * Submit answers, probably
     * For marketing purposes only!
     *
     * @param \App\Http\Requests\FormularFormRequest $request
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function storeAgain(Request $request): View|RedirectResponse
    {
        # get user
        $_user = $this->formularRepo->processStoreAgain($request);

        if (!Auth::check()) {
            return redirect()
                ->route('form.table')
                ->with('email', $_user->email)
                ->with('firstname', $_user->first_name)
                ->with('lastname', $_user->last_name);
        }

        return redirect('user/recipes/list')->with('success', trans('common.formular_user_updated'));
    }

    /**
     * Buy option to edit existing answers
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function buyEditing(Request $request): RedirectResponse
    {
        try {
            if (false === $this->formularRepo->processBuyEditing($request->user())) {
                return response()->redirectToRoute('formular.edit');
            }
        } catch (PublicException $e) {
            return back()->withErrors($e->getMessage());
        } catch (ExceptionInterface $e) {
            return back()->withErrors($e->getMessage());
        }
        return response()->redirectToRoute('formular.edit');
    }

    /**
     * Form for editing existing answers.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(): RedirectResponse|View|Factory
    {
        $user = Auth::user();
        if ($user->isFormularExist() && !$user->canEditFormular()) {
            return redirect()->route('recipes.list')->with('warning', trans('common.formular_already_exist'));
        }

        # get all active Questions
        $questions = $this->formularRepo->getSurveyQuestion();

        # get Allergy types
        $allergyTypes = AllergyTypes::with('allergies')->get();

        $answer  = [];
        $answers = $user?->formular?->answers?->toArray();
        if (!is_null($answers)) {
            array_map(
                function ($item) use (&$answer) {
                    $answer[$item['survey_question_id']] = $item;
                },
                $answers
            );
        }

        return view('formular.edit', compact('questions', 'allergyTypes', 'answer'));
    }

    /**
     * Show questions for a guest
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function tryForFree(): View|Factory
    {
        $allergyTypes = AllergyTypes::with('allergies')->get();
        $questions    = $this->formularRepo->getFormularQuestions($this->formularRepo->getSurveyQuestion());

        return view('formular.create', compact('questions', 'allergyTypes'));
    }

    /**
     * try For Free Marketing
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tryForMarketing(): View|Factory
    {
        $allergyTypes = AllergyTypes::with('allergies')->get();
        $questions    = $this->formularRepo->getFormularQuestions($this->formularRepo->getSurveyQuestion());

        return view('form.create', compact('questions', 'allergyTypes'));
    }

    /**
     * check Email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request): JsonResponse
    {
        # validate email
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email:rfc,dns',
            ]
        );

        if ($validator->fails()) {
            return response()->json(false);
        }

        # get email
        $customerEmail = $request->get('email');

        # check User exists
        $existUser = !((User::ofEmail($customerEmail)->count() == 0));

        return response()->json(!$existUser);
    }

    /**
     * Check when user can edit ones formular for free
     * @route GET /user/formular/check
     */
    public function checkEditPeriod(Request $request, FormularService $service): JsonResponse
    {
        return response()->json(
            trans(
                'common.free_change_formular_in_days',
                [
                    'amount' => config('formular.formular_editing_price_foodpoints'),
                    'number' => $service->getFreeEditPeriod($request->user())
                ]
            )
        );
    }
}
