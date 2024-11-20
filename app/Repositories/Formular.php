<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\FormularCreationMethodsEnum;
use App\Exceptions\{NoData, PublicException};
use App\Helpers\Calculation;
use App\Jobs\{ActionsAfterChangingFormular, AutomationUserCreation};
use App\Models\{SurveyQuestion, User, UserRecipeCalculatedPreliminary};
use App\Repositories\Users as UsersRepository;
use Auth;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Transaction;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Date;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Modules\Chargebee\Services\ChargebeeService;
use Modules\Course\Enums\CourseId;
use Modules\Course\Models\Course;
use Throwable;

/**
 * Class Formular Repository
 *
 * TODO: should be refactored to service and only data processes should be left here.
 * TODO: refactor validation logic with using "specification" pattern for more code readability
 * @package App\Repositories
 * @deprecated
 */
class Formular
{
    public const ANSWER_OTHERS    = 'others';
    public const ANSWER_SONSTIGES = 'sonstiges';

    /**
     * Formular constructor.
     * @param \App\Repositories\Users $userRepository
     */
    final public function __construct(private UsersRepository $userRepository)
    {
    }

    /**
     * Provide convenient formular questions keys for collection.
     *
     * @param \Illuminate\Support\Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection $surveyQuestions
     * @return array
     */
    final public function getFormularQuestions(Collection|AnonymousResourceCollection $surveyQuestions): array
    {
        $questions = [];
        foreach ($surveyQuestions as $question) {
            $questions[$question->key_code] = $question;
        }

        return $questions;
    }

    /**
     * @throws \Throwable
     */
    final public function processStore(Request $request): void
    {
        # get user

        // TODO:: @NickMost review....
        // TODO: its not correct to to create a user here, as it requires different form requests!
        $_user = Auth::check() ? Auth::user() : $this->userRepository->createUserFromRequest($request);

        /**
         * Save the where to find us answer
         * TODO: know_us is not working, even as expected
         * old check up like empty($_user->know_us), pointless because there will be string and rarely null.
         * It better just to override if new data is received.
         */
        //        if ($request->has('know_us')) {
        //            $_user->know_us = $request->get('know_us');
        //            $_user->save();
        //        }

        # remove the token
        $values = $request->all();

        if (empty($values)) {
            // TODO: to avoid this, data should be validated inside form request.
            throw new NoData('Values are not provided or provided with incorrect format');
        }

        // WEB-397  fixed issue with date formats Y-m-d, dmY

        // TODO:: refactor me, dirty-dirty quick fix...
        if (!empty($values[1]['answer'])) {
            $val = $values[1]['answer'];
            if (strpos($val, '-') !== false) {
                try {
                    $val = Date::parse($val)->format('Y-m-d');
                } catch (Throwable $e) {
                    try {
                        $val = Date::createFromFormat('d-m-Y', $val)->format('Y-m-d');
                    } catch (Throwable $e) {
                    }
                }
            } elseif (strpos($val, '.') !== false) {
                try {
                    $val = Date::createFromFormat('d.m.Y', $val)->format('Y-m-d');
                } catch (Throwable $e) {
                    try {
                        $val = Date::createFromFormat('Y.m.d', $val)->format('Y-m-d');
                    } catch (Throwable $e) {
                    }
                }
            } else {
                try {
                    $val = Date::createFromFormat('dmY', $val)->format('Y-m-d');
                } catch (Throwable $e) {
                    try {
                        $val = Date::createFromFormat('Ymd', $val)->format('Y-m-d');
                    } catch (Throwable $e) {
                    }
                }
            }
            $values[1]['answer'] = $val;
        } else {
            $values[1]['answer'] = Carbon::now()->format('Y-m-d');
        }

        if (!empty($values[4]['answer'])) {
            $val = $values[4]['answer'];
            if (str_contains($val, '-')) {
                try {
                    $val = Date::parse($val)->format('Y-m-d');
                } catch (Throwable) {
                    try {
                        $val = Date::createFromFormat('d-m-Y', $val)->format('Y-m-d');
                    } catch (Throwable) {
                    }
                }
            } elseif (str_contains($val, '.')) {
                try {
                    $val = Date::createFromFormat('d.m.Y', $val)->format('Y-m-d');
                } catch (Throwable) {
                    try {
                        $val = Date::createFromFormat('Y.m.d', $val)->format('Y-m-d');
                    } catch (Throwable) {
                    }
                }
            } else {
                try {
                    $val = Date::createFromFormat('dmY', $val)->format('Y-m-d');
                } catch (Throwable) {
                    try {
                        $val = Date::createFromFormat('Ymd', $val)->format('Y-m-d');
                    } catch (Throwable) {
                    }
                }
            }
            $values[4]['answer'] = $val;
        }
        // end of part which needs to be refactored!!!!


        $values[4]['answer'] = Date::parse($values[4]['answer'])->format('Y-m-d');

        // TODO:: hardcode fix for answer about weight WEB-161
        if (!empty($values[5]['answer'])) {
            $values[5]['answer'] = floatval(str_replace(',', '.', $values[5]['answer']));
        }
        // TODO:: hardcode fix for answer about height WEB-178
        if (!empty($values[3]['answer'])) {
            $values[3]['answer'] = floatval(str_replace(',', '.', $values[3]['answer']));
            while (!empty($values[3]['answer']) && $values[3]['answer'] < 90) {
                $values[3]['answer'] = $values[3]['answer'] * 10;
            }
        }


        // WEB-276 hardcode.... BEGIN SECTION
        // need to take care about ID 15,16  sonstiges, others
        // Sorry, too dirty trick, but it's for replacement sonstiges -> others where they are must be
        $questions = $this->getSurveyQuestion(['id', 'key_code', 'attributes'])->whereIn('id', [15, 16])->toArray();
        foreach ($questions as $q) {
            if (isset($values[$q['id']], $values[$q['id']]['answer'])) {
                if (isset($q['attributes']['show_textarea'])) {
                    if (!array_key_exists($q['attributes']['show_textarea'], $values[$q['id']]['answer'])) {
                        $val = null;

                        if (isset($values[$q['id']]['answer'][self::ANSWER_OTHERS])) {
                            $val = $values[$q['id']]['answer'][self::ANSWER_OTHERS];
                        }

                        if (is_null($val) && isset($values[$q['id']]['answer'][self::ANSWER_SONSTIGES])) {
                            $val = $values[$q['id']]['answer'][self::ANSWER_SONSTIGES];
                        }

                        if (isset($values[$q['id']]['answer'][self::ANSWER_SONSTIGES])) {
                            unset($values[$q['id']]['answer'][self::ANSWER_SONSTIGES]);
                        }

                        if (isset($values[$q['id']]['answer'][self::ANSWER_OTHERS])) {
                            unset($values[$q['id']]['answer'][self::ANSWER_OTHERS]);
                        }

                        $values[$q['id']]['answer'][$q['attributes']['show_textarea']] = $val;
                    }
                }
            }
        }
        // WEB-276 hardcode.... EN SECTION

        # get ingestions
        //$ingestions = Ingestion::where('active', true)->get()->pluck('key')->toArray();
        $isFormularExist = $_user->isFormularExist();

        list(
            'answersData'    => $answersData,
            'diffAnswer'     => $diffAnswer,
            'canRecalculate' => $canRecalculate
        ) = $this->getAnswerData($_user, $values, $isFormularExist);

        if ($diffAnswer || $isFormularExist) {
            $this->createUserFormularAndDiets($_user, $answersData);

            # check exist subscription
            if (!empty($_user->subscription)) {
                # check can Recalculate and exist subscription
                if ($canRecalculate) {
                    $this->updateUserFormularAndDiets($_user);

                    # user recipe Calculated Preliminary nilled
                    if ($_user->preliminaryCalc()->count() > 0) {
                        UserRecipeCalculatedPreliminary::where('user_id', $_user->id)
                            ->update(['valid' => null, 'counted' => 0]);
                    }

                    ActionsAfterChangingFormular::dispatch($_user)
                        ->onQueue('high')
                        ->delay(now()->addSeconds(5));
                } else {
                    # message from email send
                    send_raw_admin_email(
                        "User $_user->email (#$_user->id) has changed formular!",
                        'Formular has been changed!'
                    );
                }
            }
            return;
        }
        #use for checking all condition
        $validateData = true;
        // TODO: data of each item should be decoded in validation method as well
        // TODO: refactor with using "specification" pattern for more code readability
        // TODO: ids - worst thing that can be dane to make checks
        // TODO: validations should be performed on key_code as it is more reliable source of data, but frontend should be heqavily refactored
        if (!$isFormularExist) {
            $this->createUserFormularAndDiets($_user, $answersData);
            $keyCodeMap = config('formular.keycode_map');
            foreach ($answersData as $keys => $data) {
                switch ($keyCodeMap[$keys]) {
                    case 'growth':
                        if ($data <= 100 || $data >= 250) {
                            $validateData = false;
                        }
                        break;
                    case 'age':
                        try {
                            $age = Carbon::parse($data)->age;
                        } catch (InvalidFormatException) {
                            $age = 0;
                        }
                        if ($age < 16) {
                            $validateData = false;
                        }
                        break;
                    case 'weight':
                        if ($data <= 40 || $data >= 200) {
                            $validateData = false;
                        }
                        break;
                    case 'intensive_sports':
                        $raw = json_decode($data);
                        if ($raw->count >= 5 || $raw->time >= 60) {
                            $validateData = false;
                        }
                        break;
                    case 'moderate_sports':
                        $raw = json_decode($data);
                        if ($raw->count >= 5) {
                            $validateData = false;
                        }
                        break;
                    case 'disease':
                        $raw = json_decode($data, true);
                        // TODO: $raw->others should be treated as one type. not as partly array and partly std
                        if (
                            (
                                key_exists(self::ANSWER_SONSTIGES, (array)$raw)
                                &&
                                !is_null((array)$raw[self::ANSWER_SONSTIGES])
                                &&
                                !empty((array)$raw[self::ANSWER_SONSTIGES])
                            )
                            ||
                            (
                                key_exists(self::ANSWER_OTHERS, (array)$raw)
                                &&
                                !is_null((array)$raw[self::ANSWER_OTHERS])
                                &&
                                !empty((array)$raw[self::ANSWER_OTHERS])
                            )) {
                            $validateData = false;
                        }
                        break;
                    case 'allergy':
                        $raw = json_decode($data, true);
                        // TODO: $raw->keys should be treated as one type. not as partly array and partly std
                        if (
                            key_exists('hist', (array)$raw) ||
                            key_exists('oxalic', (array)$raw) ||
                            (
                                key_exists(self::ANSWER_SONSTIGES, (array)$raw)
                                &&
                                !is_null((array)$raw[self::ANSWER_SONSTIGES])
                                &&
                                !empty((array)$raw[self::ANSWER_SONSTIGES])
                            ) ||
                            (
                                key_exists(self::ANSWER_OTHERS, (array)$raw)
                                &&
                                !is_null((array)$raw[self::ANSWER_OTHERS])
                                &&
                                !empty((array)$raw[self::ANSWER_OTHERS])
                            )
                        ) {
                            $validateData = false;
                        }
                        break;
                    case 'any_comments':
                        if (!empty($data)) {
                            $validateData = false;
                        }
                        break;
                }
            }

            $chargebeePlanId = $_user->getLastChargebeePlanId();
            $challengeId     = ChargebeeService::getChallengeIdByChargebeePlanId($chargebeePlanId, $_user->lang);

            # create Subscription if not active found
//            if (empty($_user->challenge)) {
//                $_user->createSubscription($challengeId);
//            }

            // setup QUICK_GUIDE_CHALLENGE for user
            $_user->setFirstTimeCourse();


            $startAt = Carbon::parse($values[1]['answer']);
            if (
                $challengeId == CourseId::TBR2023->value
                ||
                $challengeId == CourseId::BOOTCAMP->value
                ||
                $challengeId == CourseId::HAPPY_BELLY->value
            ) {
                // requirements from task WEB-291
                // requirements from task WEB-415 for CHALLENGES_CHALLENGE_HAPPY_BELLY_ID
                if ($startAt < Carbon::now()) {
                    $startAt = Carbon::now()->addDays(3)->startOfDay();
                } else {
                    $startAt = $startAt->addDays(3)->startOfDay();
                }
            }

            // requirements from task WEB-513
            if (
                $challengeId == CourseId::LONGEVITY->value
            ) {
                $triggerPointDate = Carbon::createFromFormat('Y-m-d', '2023-10-23')->startOfDay();
                if ($startAt->gte($triggerPointDate)) {
                    $startAt = Carbon::now()->startOfDay();
                }
            }

            // challenge minimum start date checking
            // copy from app/Repositories/Challenges.php::processBuy
            // TODO:: refactor it
            $aboChallenge = Course::findOrFail($challengeId);
            $aboChallenge = $_user->_prepareCoursesForUser(collect([$aboChallenge]))->first();
            if (!empty($aboChallenge->minimum_start_at) && $startAt < $aboChallenge->minimum_start_at) {
                $startAt = Carbon::parse($aboChallenge->minimum_start_at)->startOfDay();
            }


            // setup proper challenge by chargebee plan id
            $_user->addCourseIfNotExists($challengeId, $startAt);

            # automation for New User After Validation
            if ($validateData) {
                $this->updateUserFormularAndDiets($_user);

                # user recipe Calculated Preliminary nilled
                if ($_user->preliminaryCalc()->count() > 0) {
                    UserRecipeCalculatedPreliminary::where('user_id', $_user->id)
                        ->update(['valid' => null, 'counted' => 0]);
                }

                #Running job for distribute random recipes and generate recipes
                AutomationUserCreation::dispatch($_user)
                    ->onQueue('high')
                    ->delay(now()->addMinutes(5));
            } else {
                # Add foodpoints for all new users
                try {
                    $_user->deposit(config('formular.new_user_foodpoints_bonus'), ['description' => 'Welcome User Bonus']);
                } catch (Throwable $e) {
                    logError($e);
                    // notify admin to add coins manually
                    send_raw_admin_email(
                        "User $_user->email (#$_user->id) did not receive welcome foodpoint due to error. Please assign manually.",
                        'Welcome bonus error for user'
                    );
                }

                $now = Carbon::now();
                #check user is new
                if (($_user->created_at->diffInDays($now) < config('formular.expiration_period_in_days')) && $_user->status) {
                    send_raw_admin_email(
                        "User $_user->email (#$_user->id) has marked special needs!",
                        'Nutritionist needs to check'
                    );
                }
            }
        }
    }

    /**
     * Get answers data of user.
     * TODO: refactor this part. Totally legacy part
     * @param \App\Models\User $_user
     * @param array $values
     * @param bool $isFormularExist
     * @return array
     */

    // ??? @NickMost for review
    final public function getAnswerData(User $_user, array $values, bool $isFormularExist): array
    {
        # prepare answer data
        $answersData = [];

        # different answer flag
        $diffAnswer = false;

        # can Recalculate Recipes flag
        $canRecalculate = $_user->calc_auto;

        # get all active questions
        $questions          = $this->getSurveyQuestion(['id', 'key_code', 'order', 'active']);
        $formularCollection = $_user->formular;

        foreach ($questions as $question) {
            # set default value
            $value = null;

            # check answer
            $answer = isset($values[$question->id]['answer']) && !is_null($values[$question->id]['answer']) ?
                $values[$question->id]['answer'] :
                '';
            if (key_exists($question->id, $values) && $answer) {
                # get value by question
                $value = $values[$question->id];

                if (is_string($answer) && strpos($answer, '|')) {
                    $_value = explode('|', $answer);
                    if (is_array($_value) && (count($_value) === 2)) {
                        $_value = [$_value[0] => $_value[1]];
                    }
                    $answer = json_encode($_value);
                } else {
                    $answer = is_array($answer) ? json_encode($answer) : $answer;
                }
            }

            $answersData[$question->id] = $answer;

            if ($isFormularExist) {
                $oldAnswer = $formularCollection->answers->where('survey_question_id', $question->id)->first();
                if (empty($oldAnswer) || strcasecmp($oldAnswer->answer, $answer) != 0) {
                    $diffAnswer = true;
                }
            }

            # =====================================
            # check can Recalculate Recipe for user
            # =====================================
            // TODO: refactor with using "specification" pattern for more code readability
            if ($canRecalculate) {
                switch ($question->key_code) {
                    case 'disease':
                        $answerKeyExists = $answerValue = null;
                        if ($isFormularExist) {
                            $oldAnswer       = $formularCollection->answers->where('survey_question_id', $question->id)->first();
                            $oldAnswerDecode = (array)json_decode($oldAnswer->answer, true);

                            $answerKeyExists = key_exists(self::ANSWER_OTHERS, $value['answer']) ||
                                key_exists(self::ANSWER_SONSTIGES, $value['answer']);
                            $oldAnswerKeyExists = key_exists(self::ANSWER_OTHERS, $oldAnswerDecode) ||
                                key_exists(self::ANSWER_SONSTIGES, $oldAnswerDecode);
                            $answerValue = $value['answer'][self::ANSWER_OTHERS] ?? $value['answer'][self::ANSWER_SONSTIGES] ?? null;

                            $oldAnswerDecodedArray = $oldAnswerDecode;
                            $answerValueOld        = null;
                            if (isset($oldAnswerDecodedArray[self::ANSWER_OTHERS])) {
                                $answerValueOld = $oldAnswerDecodedArray[self::ANSWER_OTHERS];
                            }
                            if (isset($oldAnswerDecodedArray[self::ANSWER_SONSTIGES])) {
                                $answerValueOld = $oldAnswerDecodedArray[self::ANSWER_SONSTIGES];
                            }

                            if (
                                ($answerKeyExists
                                    &&
                                    $oldAnswerKeyExists
                                    &&
                                    strcasecmp($answerValueOld, $answerValue) != 0)
                                ||
                                ($answerKeyExists
                                    &&
                                    !$oldAnswerKeyExists)

                                // TODO:: check if answer was exists and with new formular doesn't exists, what to do?
                                ||
                                (!$answerKeyExists
                                    &&
                                    $oldAnswerKeyExists)
                            ) {
                                $canRecalculate = false;
                            }
                        } elseif ($answerKeyExists && !is_null($answerValue)) {
                            $canRecalculate = false;
                        }
                        break;
                    case 'allergy':
                        $answerKeyExists = $answerValue = null;
                        if ($isFormularExist) {
                            $oldAnswer       = $formularCollection->answers->where('survey_question_id', $question->id)->first();
                            $oldAnswerDecode = (array)json_decode($oldAnswer->answer, true);

                            $answerKeyExists = key_exists(self::ANSWER_SONSTIGES, $value['answer']) ||
                                key_exists(self::ANSWER_OTHERS, $value['answer']);
                            $oldAnswerKeyExists = key_exists(self::ANSWER_SONSTIGES, $oldAnswerDecode) ||
                                key_exists(self::ANSWER_OTHERS, $oldAnswerDecode);

                            $answerValue = $value['answer'][self::ANSWER_OTHERS] ?? $value['answer'][self::ANSWER_SONSTIGES] ?? null;

                            $oldAnswerDecodedArray = $oldAnswerDecode;
                            $answerValueOld        = null;
                            if (isset($oldAnswerDecodedArray[self::ANSWER_OTHERS])) {
                                $answerValueOld = $oldAnswerDecodedArray[self::ANSWER_OTHERS];
                            }
                            if (isset($oldAnswerDecodedArray[self::ANSWER_SONSTIGES])) {
                                $answerValueOld = $oldAnswerDecodedArray[self::ANSWER_SONSTIGES];
                            }

                            if (
                                ($answerKeyExists
                                    &&
                                    $oldAnswerKeyExists
                                    &&
                                    strcasecmp($answerValueOld, $answerValue) != 0)
                                ||
                                ($answerKeyExists
                                    &&
                                    !$oldAnswerKeyExists)

                                // TODO:: check if answer was exists and with new formular doesn't exists, what to do?
                                ||
                                (!$answerKeyExists
                                    &&
                                    $oldAnswerKeyExists)
                            ) {
                                $canRecalculate = false;
                            }
                        } elseif ($answerKeyExists && !is_null($answerValue)) {
                            $canRecalculate = false;
                        }
                        break;
                    case 'any_comments':
                        if ($isFormularExist) {
                            $oldAnswer = $formularCollection->answers->where('survey_question_id', $question->id)->first();
                            if (isset($value['answer']) && !is_null($oldAnswer) && strcasecmp(
                                $oldAnswer->answer,
                                $value['answer']
                            ) != 0) {
                                $canRecalculate = false;
                            }
                        } elseif (!is_null($value) && $value != '') {
                            $canRecalculate = false;
                        }
                        break;
                }
            }
        }

        return compact('answersData', 'diffAnswer', 'canRecalculate');
    }

    /**
     * Retrieve Survey questions
     *
     * @param string[] $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    final public function getSurveyQuestion($columns = ['*'])
    {
        return SurveyQuestion::where('active', true)->get($columns)->sortBy('order');
    }

    /**
     * Create new formular and diets data.
     */
    final public function createUserFormularAndDiets(User $_user, array $answersData): void
    {
        # create new formular
        /**
         * TODO: pay attention to this. maybe some extra checks needed?
         * TODO: not all cases are covered
         * @note for now, we cannot send any extra data in requests, too complicated, so
         * we check if user has paid for formular in last 10 minutes
         */
        $creation_method = FormularCreationMethodsEnum::FREE;
        if ($_user->transactions()
            ->whereBetween('created_at', [now()->subMinutes(10), now()])
            ->where([
                ['type', Transaction::TYPE_WITHDRAW],
                ['amount', (int)config('formular.formular_editing_price_foodpoints') * -1]
            ])
            ->exists()) {
            $creation_method = FormularCreationMethodsEnum::PAID;
        }

        $formular = $_user->formulars()->create(
            ['approved' => false, 'creation_method' => $creation_method]
        );

        $newAnswersData = [];
        foreach ($answersData as $key => $answer) {
            $newAnswersData[] = [
                'user_id'            => $_user->getKey(),
                'survey_question_id' => $key,
                'answer'             => $answer
            ];
        }

        # sort formular answers by question Ids
        usort($newAnswersData, fn($a, $b) => $a['survey_question_id'] - $b['survey_question_id']);

        # create formular answers
        $formular->answers()->createMany($newAnswersData);

        # clear formular cache
        $_user->forgetFormularCache();

        # check empty dietData
        if (empty($_user->dietdata)) {
            if ($_user->isFormularExist() && $dietData = Calculation::calcUserNutrients($_user->id)) {
                $_user->dietdata = $dietData;
                $_user->save();
            }
        }
    }

    /**
     * Update existing user formular and diets values.
     *
     * @param \App\Models\User $_user
     */
    final public function updateUserFormularAndDiets(User $_user): void
    {
        # approve formular
        $_user->formular->update(['approved' => true]);
        # clear formular cache
        $_user->forgetFormularCache();
        # calc dietData
        $_user->dietdata = Calculation::calcUserNutrients($_user->id);
        $_user->save();
    }

    /*May god forgive me for the poor naming*/

    /**
     * @param $request
     * @return \App\Models\User|null
     */
    final public function processStoreAgain($request): ?User
    {
        # get user
        $_user = Auth::check() ? Auth::user() : $this->userRepository->createUserFromRequest($request);

        # remove the token
        $values              = $request->except('_token');
        $values[1]['answer'] = !empty($values[1]['answer']) ? Date::parse($values[1]['answer'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $values[4]['answer'] = Date::parse($values[4]['answer'])->format('Y-m-d');
        // TODO: hardcode fix for answer about weight WEB-161
        if (!empty($values[5]['answer'])) {
            $values[5]['answer'] = floatval(str_replace(',', '.', $values[5]['answer']));
        }
        // TODO:: hardcode fix for answer about height WEB-178
        if (!empty($values[3]['answer'])) {
            $values[3]['answer'] = floatval(str_replace(',', '.', $values[3]['answer']));
            while (!empty($values[3]['answer']) && $values[3]['answer'] < 90) {
                $values[3]['answer'] = $values[3]['answer'] * 10;
            }
        }

        if (empty($values)) {
            return $_user;
        }
        # get ingestions
        //$ingestions = Ingestion::where('active', true)->get()->pluck('key')->toArray();
        $isFormularExist = $_user->isFormularExist();

        list(
            'answersData'    => $answersData,
            'diffAnswer'     => $diffAnswer,
            'canRecalculate' => $canRecalculate
        ) = $this->getAnswerData($_user, $values, $isFormularExist);

        if ($diffAnswer || !$isFormularExist) {
            $this->createUserFormularAndDiets($_user, $answersData);

            # check exist subscription
            if (!empty($_user->challenge)) {
                # check can Recalculate and exist subscription
                if ($canRecalculate) {
                    $this->updateUserFormularAndDiets($_user);

                    # user recipe Calculated Preliminary nilled
                    if ($_user->preliminaryCalc()->count() > 0) {
                        UserRecipeCalculatedPreliminary::where('user_id', $_user->id)
                            ->update(['valid' => null, 'counted' => 0]);
                    }

                    ActionsAfterChangingFormular::dispatch($_user)
                        ->delay(now()->addSeconds(5));
                } else {
                    # message from email send
                    send_raw_admin_email(
                        "User $_user->email (#$_user->id) has changed formular!",
                        'Formular has been changed!'
                    );
                }
            }
        }

        # set cookie 20d * 24h * 60m
        //Cookie::queue('formular_edited', true, 28800);
        return $_user;
    }

    /**
     * Handling opportunity to buying formular editing.
     *
     * @param User $user
     *
     * @return bool
     *
     * @throws \App\Exceptions\PublicException
     * @throws \Bavix\Wallet\Internal\Exceptions\ExceptionInterface
     */
    final public function processBuyEditing(User $user): bool
    {
        $editingPrice       = config('formular.formular_editing_price_foodpoints');
        $enabledForFrontend = boolval(config('formular.ability_forced_formular_editing_by_client_enabled'));

        // check if feature enabled for fronted
        if (!$user->isFormularExist() || !$enabledForFrontend) {
            throw new PublicException(trans('api.formular_edit_prohibited'));
        }

        // check if formular can be edited for free. if so - just redirect to editing page
        if ($user->canEditFormular()) {
            return false;
        }

        // check user has enough foodpoints on account
        if (!$user->canWithdraw($editingPrice)) {
            throw new InsufficientFunds(
                trans(
                    'common.you_must_have_at_least_:amount_food_points_on_your_account',
                    ['amount' => config('formular.formular_editing_price_foodpoints')]
                )
            );
        }

        // proceed with editing - withdraw foodpoints from balance and force formular for editing
        try {
            $user->withdraw($editingPrice, ['description' => 'Purchase of Formular edit']);
            $user->formular->forceVisibility();
        } catch (ExceptionInterface $e) {
            logError($e);
            throw $e;
        }

        return true;
    }
}
