<?php

namespace Modules\Internal\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Course\Enums\CourseId;

/** @internal
 * @deprecated
 * */
class sportChallengeChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checker:challenges-sport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and add sport challenges to the users account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $oneYearPeriod = now()->subYear();

        $tbrUsersIds = \DB::table('course_users')
            ->whereIn(
                'course_id',
                [
                    CourseId::TBR2024_DE->value,
                    //                                  AboChallenges::CHALLENGES_CHALLENGE_TBR2024_EN_ID
                ]
            )
            ->where('start_at', '>', $oneYearPeriod)
            ->pluck('user_id')
            ->toArray();

        $sportUsersIds = \DB::table('abo_challenges_users')
            ->where('course_id', CourseId::SPORT->value)
            ->where('start_at', '>', $oneYearPeriod)
            ->pluck('user_id')
            ->toArray();


        $usersWithoutSport = array_diff($tbrUsersIds, $sportUsersIds);
        sort($usersWithoutSport);
        $usersWithoutSport = array_unique($usersWithoutSport);


        $results = [];
        if (!empty($usersWithoutSport)) {
            User::whereIn('id', $usersWithoutSport)->where('status', '=', 1)->orderBy('id', 'DESC')->chunk(
                1000,
                function ($users) use (&$results) {
                    foreach ($users as $user) {
                        if (
                            (
                                $user->challengeExists(CourseId::TBR2024_DE->value)
                                //                                ||
                                //                                $user->challengeExists(AboChallenges::CHALLENGES_CHALLENGE_TBR2024_EN_ID)
                            )
                            &&
                            !$user->challengeExists(CourseId::SPORT->value)
                        ) {
                            $challengesData = $user->aboChallenges()->withPivot('start_at')->whereIn('courses.id', [
                                CourseId::TBR2024_DE->value,
                                CourseId::TBR2024_EN->value
                            ])->orderBy('start_at', 'DESC')->first()->toArray();

                            $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $challengesData['pivot']['start_at']);
                            $user->addChallengeIfNotExists(CourseId::SPORT->value, $startAt);
                            $results[$user->id][] = 'Added challenge ' . CourseId::SPORT->value . ' with start date ' . $startAt;
                        }
                    }
                }
            );
        }
        dump('Total amount of affected users = ' . count($results));
        if (!empty($results)) {
            dump($results);
        }

        return 1;
    }
}
