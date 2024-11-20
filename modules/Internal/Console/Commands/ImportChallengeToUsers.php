<?php

declare(strict_types=1);

namespace Modules\Internal\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/** @internal */
class ImportChallengeToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:challenges-users {challenge_id : challenge ID} {start_date? : Date of challenge start YYYY-MM-DD} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Added challenges to the users account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('');
        $this->info('Adding challenges to all users started...');
        $this->info('');

        $startAt      = trim(strtolower($this->argument('start_date')));
        $aboChallenge = intval($this->argument('challenge_id'));

        if (empty($startAt)) {
            $startAt = Carbon::now()->startOfDay();
        } else {
            $startAt = Carbon::createFromFormat('Y-m-d', $startAt)->startOfDay();
        }

        $users = [];

        #get imported file which you want and read
        if (($open = fopen(public_path() . "/importchallenges.csv", "r")) !== false) {
            while (($data = fgetcsv($open, 1000, ",")) !== false) {
                foreach (array_values($data) as $email) {
                    $email = strtolower(trim($email));
                    $email = mb_convert_encoding($email, 'UTF-8', 'UTF-8');
                    $email = str_replace('?', '', $email);
                    if (!empty($email)) {
                        $users[] = $email;
                    }
                }
            }
            fclose($open);
        }

        $users  = array_unique($users);
        $emails = [];
        foreach ($users as $item) {
            $emails[] = trim(strtolower($item));
        }

        $users = array_unique($emails);
        sort($users);

        $prompt = 'Are you want to add challenge ID ' . $aboChallenge . ' with start date' . $startAt->format('Y-m-d') . ' for ' . count(
            $emails
        ) . ' of users.' . PHP_EOL;
        $prompt .= 'Emails: ' . implode(',', $users);
        if (self::confirm($prompt)) {
            $usercount  = 0;
            $user2count = 0;

            foreach ($users as $userEmail) {
                $userData = User::where('email', $userEmail)->get();

                if (empty($userData)) {
                    dd('not exists user ' . $userEmail);
                }
                foreach ($userData as $_user) {
                    if (!empty($_user->id)) {
                        $allowToImport = true;

                        $existsChallenge = $_user->courses()->where('course_id', $aboChallenge)->first()?->toArray();
                        if ($existsChallenge && isset($existsChallenge['pivot'], $existsChallenge['pivot']['start_at'])) {
                            $startDate       = $existsChallenge['pivot']['start_at'];
                            $userDataStartAt = Carbon::createFromFormat('Y-m-d H:i:s', $startDate)->startOfDay();
                            if ($userDataStartAt->greaterThanOrEqualTo($startAt)) {
                                $allowToImport = false;
                                $this->info('Issue with ' . $userEmail . ' user has start date: ' . $startDate);
                            }
                        }


                        if ($allowToImport) {
                            $added = $_user->addCourseIfNotExists($aboChallenge, $startAt);
                            if ($added) {
                                $this->info('Challenge added into...' . $userEmail);
                                $usercount++;
                            }
                        }
                    }
                }
                $user2count++;
            }
            $this->info('Script End...');
            $this->info("Imported challenges: \t" . $usercount);
            $this->info("Total amount of users: \t" . $user2count);
        }

        return 1;
    }
}
