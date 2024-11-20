<?php

declare(strict_types=1);

namespace Modules\Internal\Console\Commands;

use App\Enums\ChargeBeeSubscriptionStatusEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Chargebee\Models\ChargebeeSubscription;
use Modules\Chargebee\Services\ChargebeeService;

/** @internal */
class fixChallengeChargebeeAndMealPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix20231229';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix issue with challenges and meal plan';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $usersIssues = [];

        $now           = now();
        $oneYearPeriod = now()->subMonths(6);
        dump('subscriptions from chargebee from ' . $oneYearPeriod->format('Y-m-d H:i:s'));

        /*
        $tbrUsersIds = \DB::table('abo_challenges_users')
                          ->whereIn(
                              'abo_challenge_id',
                              [
                                  AboChallenges::CHALLENGES_CHALLENGE_TBR2024_DE_ID,
                                  AboChallenges::CHALLENGES_CHALLENGE_TBR2024_EN_ID
                              ]
                          )
                          ->where('start_at', '>', $oneYearPeriod)
                          ->pluck('user_id')
                          ->toArray();

        $sportUsersIds = \DB::table('abo_challenges_users')
                            ->where('abo_challenge_id', AboChallenges::CHALLENGES_CHALLENGE_SPORT)
                            ->where('start_at', '>', $oneYearPeriod)
                            ->pluck('user_id')
                            ->toArray();


        $usersWithoutSport = array_diff($tbrUsersIds, $sportUsersIds);
        sort($usersWithoutSport);
        $usersWithoutSport = array_unique($usersWithoutSport);


        User::whereIn('id', $usersWithoutSport)->where('status', '=', 1)->orderBy('id', 'DESC')->chunk(
            1000,
            function ($users) use ($now, &$usersIssues) {
                foreach ($users as $user) {
                    if (
                        ($hasTBRDe = $user->challengeExists(
                                $challengeId = AboChallenges::CHALLENGES_CHALLENGE_TBR2024_DE_ID
                            ) || $hasTBREn = $user->challengeExists(
                                $challengeId = AboChallenges::CHALLENGES_CHALLENGE_TBR2024_EN_ID
                            ))
                        &&
                        !$user->challengeExists($challengeId = AboChallenges::CHALLENGES_CHALLENGE_SPORT)
                    ) {
                        if (isset($hasTBRDe) && $hasTBRDe) {
                            $usersIssues[$user->id][] = 'not exists SPORT challenge '.AboChallenges::CHALLENGES_CHALLENGE_SPORT.', user lang = '.$user->lang.', user has challenge = '.AboChallenges::CHALLENGES_CHALLENGE_TBR2024_DE_ID;
                        } elseif (isset($hasTBREn) && $hasTBREn) {
                            $usersIssues[$user->id][] = 'not exists SPORT challenge '.AboChallenges::CHALLENGES_CHALLENGE_SPORT.', user lang = '.$user->lang.', user has challenge = '.AboChallenges::CHALLENGES_CHALLENGE_TBR2024_EN_ID;
                        }
                    }
                }
            }
        ); */


        ChargebeeSubscription::whereNotNull('assigned_user_id')->whereNull('processed')->where(
            'created_at',
            '>',
            $oneYearPeriod
        )->orderBy('id', 'DESC')->chunk(
            1000,
            function ($chargebeePlans) use ($now, &$usersIssues) {
                foreach ($chargebeePlans as $item) {
                    $startedAt = false;

                    if (isset($item->data['started_at'])) {
                        $startedAt = Carbon::createFromFormat('d.m.Y', $item->data['started_at']);
                    }
                    if (isset($item->data['created_at'])) {
                        $startedAt = Carbon::createFromTimestamp(intval($item->data['created_at']));
                    }

                    if ($startedAt) {
                        $subscriptionStartedAtDiffToday = $startedAt->diffInDays($now, false);

                        if (
                            empty($item->data['cancelled_at'])
                            &&
                            in_array(
                                $item->data['status'],
                                ChargeBeeSubscriptionStatusEnum::potentiallyActiveStatus()
                            )
                            &&
                            $subscriptionStartedAtDiffToday < 120
                        ) {
                            $user = User::find($item->assigned_user_id);


                            if ($user && $user->status) {
                                if (
                                    // TODO:: review that, as plan_id in subscription_items
                                    isset($item->data, $item->data['plan_id'])
                                    ||
                                    isset($item->data['subscription_items'])
                                ) {

                                    $planId = ChargebeeService::getChargebeePlanIdFromSubscriptionData($item->data);

                                    // TODO:: review that
                                    $challengeId = ChargebeeService::issetChallengeIdByChargebeePlanId($planId, $user->lang);

                                    //                                    $issueWithChallenge = false;
                                    if (!empty($challengeId) && !$user->courseExists($challengeId)) {
                                        $usersIssues[$user->id][] = 'not exists challenge ' . $challengeId . ', user lang = ' . $user->lang . ', planId = ' . $planId . ', subscription created ' . $startedAt->format(
                                            'Y-m-d H:i:s'
                                        );

                                        //                                        $issueWithChallenge = true;
                                        //                            $user->addChallengeIfNotExists($challengeId);
                                        //                                        dump('user_id:'.$item->assigned_user_id);
                                        //                                        dump('$challengeId:'.$challengeId);
                                    }

                                    // check if issue with not exists challenge

                                    $questionnaireExists = $user->isQuestionnaireExist();
                                    $isApproved          = $questionnaireExists && $user->questionnaireApproved === true;
                                    if ($isApproved) {
                                        // check if exists meal plan
                                        $existsRecipesForToday = $user->recipes()->where('meal_date', '>', now())->exists();
                                        if (!$existsRecipesForToday) {
                                            $usersIssues[$user->id][] = 'questionnaire approved, but not exists meal plan for now , planId = ' . $planId . ', subscription created ' . $startedAt->format(
                                                'Y-m-d H:i:s'
                                            );
                                        }
                                    }

                                    if ($questionnaireExists && $user->questionnaireApproved == false) {
                                        $usersIssues[$user->id][] = 'questionnaire NOT approved, planId = ' . $planId . ', subscription created ' . $startedAt->format(
                                            'Y-m-d H:i:s'
                                        );
                                    }
                                    //

                                    // checking welcome bonus
                                    if ($user->balance == 0 && $questionnaireExists) {
                                        $usersIssues[$user->id][] = 'balance 0, planId = ' . $planId . ', subscription created ' . $startedAt->format(
                                            'Y-m-d H:i:s'
                                        );
                                    }

                                    if (!$questionnaireExists) {
                                        $usersIssues[$user->id][] = 'questionnaire still NOT exists, planId = ' . $planId . ', subscription created ' . $startedAt->format(
                                            'Y-m-d H:i:s'
                                        );
                                    }
                                }
                            }
                        }
                    }
                    // check if exists "welcome bonus"...
                    // get user
                    // check if exists challenge


                }
            }
        );

        $byUserId = $usersIssues;
        krsort($byUserId);
        dump('Total amount: ' . count($usersIssues));
        dump('------------- by chargebee subscription date ------------------');
        dump($usersIssues);
        dump('-------------------------------------------');
        dump('-------------------------------------------');
        dump('-------------------------------------------');
        dump('-------------------------------------------');
        dump('------------- by user ID ------------------');
        dump($byUserId);


        return 1;
    }
}
