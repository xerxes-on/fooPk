<?php

namespace Modules\Internal\Console\Commands;

use App\Enums\DatabaseTableEnum;
use App\Enums\Questionnaire\QuestionnaireQuestionIDEnum;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Helper command to export users with lipedema.
 * @internal
 * @package App\Console\Commands\Internal
 */
final class ExportUsersWithLipedema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internal:export-lipedema-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export users with lipedema';

    private Collection|array $data;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->getUsersWithLipedema()->collectMissingFields()->prepareOutput()->exportCsv();
    }

    private function getUsersWithLipedema(): ExportUsersWithLipedema
    {
        $this->data = DB::table('users')
            ->join('questionnaires', function (JoinClause $clause) {
                $clause->on('users.id', '=', 'questionnaires.user_id');
            })
            ->join('questionnaire_answers', function (JoinClause $clause) {
                $clause->on('questionnaires.id', '=', 'questionnaire_answers.questionnaire_id');
            })
            ->whereRaw(
                sprintf(
                    '%1$s.id = (select MAX(%1$s.id) from %1$s where %1$s.user_id = %2$s.id)',
                    DatabaseTableEnum::QUESTIONNAIRE,
                    DatabaseTableEnum::USERS,
                )
            )
            ->where(function (Builder $query) {
                $query->where('questionnaire_answers.answer', 'like', '%Lipedema%')
                    ->orWhere('questionnaire_answers.answer', 'like', '%Lipoedema%');
            })
            ->orderBy('users.id')
            ->get([
                'users.id as user_ID',
                'users.email as email_address',
                'users.status as enabled',
                'questionnaires.id as questionnaire_id',
                'questionnaire_answers.answer',
            ]);
        return $this;
    }

    private function collectMissingFields(): ExportUsersWithLipedema
    {
        $this->data->each(function ($item) {
            $item->chargeBee = 'no';
            $chargeBee       = DB::table('chargebee_subscriptions')
                ->where('user_id', $item->user_ID)
                ->get(['data'])->toArray();
            foreach ($chargeBee as $value) {
                $value = json_decode($value->data, true);
                if (isset($value['status']) && $value['status'] === 'active') {
                    $item->chargeBee = 'yes';
                }
            }

            $item->goal = DB::table('questionnaire_answers')
                ->where('questionnaire_id', $item->questionnaire_id)
                ->where('questionnaire_question_id', QuestionnaireQuestionIDEnum::MAIN_GOAL)
                ->get(['answer'])->first()?->answer ?? '';

            $item->birthdate = DB::table('questionnaire_answers')
                ->where('questionnaire_id', $item->questionnaire_id)
                ->where('questionnaire_question_id', QuestionnaireQuestionIDEnum::BIRTHDATE)
                ->get(['answer'])->first()?->answer ?? '';
        });
        return $this;
    }

    private function prepareOutput(): ExportUsersWithLipedema
    {
        $orderedOutput = [];
        foreach ($this->data as $value) {
            $orderedOutput[] = [
                'Email address' => $value->email_address,
                'User ID'       => $value->user_ID,
                'Birthday'      => $value->birthdate,
                'Goal'          => trans_fb(
                    'questionnaire.questions.main_goal.options.' . $value->goal,
                    locale: 'en'
                ),
                'Enabled'                       => $value->enabled ? 'Yes' : 'No',
                'Chargebee subscription active' => $value->chargeBee,
            ];
        }
        $this->data = $orderedOutput;
        return $this;
    }

    private function exportCsv(): void
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="export.csv";');

        // clean output buffer
        ob_end_clean();
        ob_start();
        $handle = fopen('php://output', 'w');

        // use keys as column titles
        fputcsv($handle, array_keys((array)$this->data['0']), ';');

        foreach ($this->data as $value) {
            fputcsv($handle, (array)$value, ';');
        }

        fclose($handle);

        // flush buffer
        ob_flush();

        // use exit to get rid of unexpected output afterward
        exit();
    }
}
