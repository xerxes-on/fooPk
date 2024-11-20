<?php

namespace Modules\Internal\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Use for generation SQL script for with whole pack of user's data
 *
 * @internal
 *
 * @package App\Console\Commands
 */
final class GenerateUserImportSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internal_generate_user_import_sql {user : The Email of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use for generation SQL script for with whole pack of user\'s data';

    private $generateSingleFile;
    private $fileHandler;
    private $userId;
    private $tablesFields;
    private $tmpDir;
    private $fileName;
    private $fullFileName;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        # get Email argument
        $userEmail = $this->argument('user');


        if (!$userEmail) {
            return Command::INVALID;
        }

        # get user data
        $userData = is_null($userEmail) ? User::active() : User::ofEmail($userEmail);
        $userData = $userData->first();

        $this->userId = $userId = $userData->id;

        $this->tmpDir = public_path() . "/tmp/";

        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir);
        }

        $this->tablesFields = [
            'users' => [
                '_primary' => 'id',
                'fields'   => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'chargebee_id',
                    'password',
                    'dietdata',
                    'remember_token',
                    'lang',
                    'calc_auto',
                    'status',
                    'know_us',
                    'profile_picture_path',
                    'notes',
                    'push_notifications',
                    'created_at',
                    'updated_at'
                ],
                'dateFields' => ['created_at', 'updated_at'],

            ],
            'user_recipe_calculated_preliminaries' => [
                '_primary' => 'id',
                'fields'   => [
                    'id',
                    'user_id',
                    'valid',
                    'invalid',
                    'counted',
                    'created_at',
                    'updated_at'
                ],
                //                'dateFields' => ['created_at', 'updated_at'],
                'dateFields' => [],

            ],
            'user_recipe_calculated' => [
                '_primary' => 'id',
                'fields'   => [
                    'id',
                    'user_id',
                    'recipe_id',
                    'custom_recipe_id',
                    'ingestion_id',
                    'invalid',
                    'recipe_data',
                    'created_at',
                    'updated_at'
                ],
                //                'dateFields' => ['created_at', 'updated_at'],
                'dateFields' => [],

            ],
        ];


        $this->generateSingleFile = true;
        $this->table_users($userData);
        $this->table_user_recipe_calculated_preliminaries();
        $this->table_user_recipe_calculated();
        $this->table_user_recipe();
        $this->table_recipes_to_users();
        $this->table_chargebee_subscriptions();
        //		$this->table_formulars();
        //		$this->table_survey_answers();

        $this->table_client_notes();
        $this->table_abo_challenges_users();

        $this->table_custom_recipes();
        $this->table_custom_recipe_categories();
        $this->table_user_bulk_exclusions();
        $this->table_user_excluded_ingredients();
        $this->table_user_excluded_recipes();
        $this->table_user_to_challenge();
        $this->table_favorites();
        $this->table_diary_datas();
        $this->table_flexmeal_lists();
        $this->table_flexmeal_to_users();
        $this->table_wallets_v2();
        $this->table_transactions();
        $this->table_posts();

        ///
        //user_recipe_calculated_preliminaries ++
        //user_recipe_calculated ++
        //user_recipe ++
        //recipes_to_users ++
        //purchase_lists
        //purchase_list_recipes
        //purchase_list_ingredients
        //abo_challenges_users ++
        //client_notes ++
        //chargebee_subscriptions ++
        //custom_recipes ++
        //custom_recipe_categories ++
        //diary_datas ++
        //favorites ++
        //flexmeal_lists ++
        //flexmeal_to_users ++
        //formulars ++
        //survey_answers ++
        //orders_histories ++
        //DO NOT RESTORE personal_access_tokens
        //posts
        //wallets_v2 ++
        //transactions
        //user_bulk_exclusions ++
        //user_excluded_ingredients ++
        //user_excluded_recipes ++
        //user_to_challenge ++


        //            $url = config('app.url').'/tmp/'.$this->fileName;
        //            var_dump($url);

        return Command::SUCCESS;
    }

    private function valueInt($value = null)
    {
        return intval($value);
    }

    private function valueFloat($value = null)
    {
        return floatval($value);
    }

    private function valueNullableInt($value = null)
    {
        return (is_null($value) ? ("NULL") : intval($value));
    }

    private function valueNullableFloat($value = null)
    {
        return (is_null($value) ? ("NULL") : floatval($value));
    }

    private function valueNullableString($value = null)
    {
        return (is_null($value) ? ("NULL") : "'" . $value . "'");
    }

    private function valueString($value = null)
    {
        return "'" . $value . "'";
    }

    private function valueDates($value = null)
    {
        return $this->valueNullableString($value);
    }

    private function removeNewLines($sqlRow)
    {
        $sqlRow = str_replace(array("\n", "\r", "	"), '', $sqlRow);
        $sqlRow = preg_replace('/ {2,}/', ' ', $sqlRow);
        return $sqlRow;
    }

    private function writeIntoFile($sqlRow, $table = null)
    {
        if (!empty($sqlRow)) {
            if ($this->generateSingleFile && empty($this->fileName)) {
                $this->fileName = 'import_' . $this->userId . '_' . date(
                    'Ymd_His'
                ) . ".sql";
                $this->fullFileName = $this->tmpDir . $this->fileName;
            } elseif (!$this->generateSingleFile) {
                $this->fileName = 'import_' . $this->userId . ((!$this->generateSingleFile && !empty($table)) ? '_' . $table : '') . '_' . date(
                    'Ymd_His'
                ) . ".sql";
                $this->fullFileName = $this->tmpDir . $this->fileName;

                @unlink($this->fullFileName);
            }

            if (($this->fileHandler = fopen($this->fullFileName, "a")) !== false) {
                fwrite($this->fileHandler, $sqlRow . PHP_EOL);
            }
            fclose($this->fileHandler);
        }
    }

    private function table_users($userData)
    {
        $table      = 'users';
        $primary    = $this->tablesFields[$table]['_primary'];
        $fields     = $this->tablesFields[$table]['fields'];
        $dateFields = $this->tablesFields[$table]['dateFields'];
        $values     = [];
        foreach ($fields as $field) {
            $val = $userData->$field;
            if (in_array($field, $dateFields)) {
                $values[$field] = $val->toDateTimeString();
            } elseif (is_array($val) || is_object($val)) {
                $values[$field] = json_encode($val);
            } else {
                $values[$field] = (string)$val;
            }
        }
        $sqlRow = "INSERT INTO $table (`" . implode('`,`', $fields) . "`) VALUES ('" . implode(
            "','",
            $values
        ) . "') ON DUPLICATE KEY UPDATE  ";

        if (($key = array_search($primary, $fields)) !== false) {
            unset($fields[$key]);
        }

        foreach ($fields as $field) {
            $sqlRow .= " `" . $field . "`='" . $values[$field] . "',";
        }
        $sqlRow = substr($sqlRow, 0, strlen($sqlRow) - 1);
        $sqlRow .= ";";
        $sqlRow = $this->removeNewLines($sqlRow);
        $this->writeIntoFile($sqlRow, $table);
    }

    private function table_user_recipe_calculated_preliminaries()
    {
        $table      = 'user_recipe_calculated_preliminaries';
        $primary    = $this->tablesFields[$table]['_primary'];
        $fields     = $this->tablesFields[$table]['fields'];
        $dateFields = $this->tablesFields[$table]['dateFields'];
        $values     = [];

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->first();

        if (empty($items)) {
            return false;
        }

        foreach ($fields as $field) {
            $val = $items->$field;
            if (in_array($field, $dateFields)) {
                $values[$field] = $val->toDateTimeString();
            } elseif (is_array($val) || is_object($val)) {
                $values[$field] = json_encode($val);
            } else {
                $values[$field] = $val;
            }
        }
        $sqlRow = "INSERT INTO $table (`" . implode('`,`', $fields) . "`) VALUES ('" . implode(
            "','",
            $values
        ) . "') ON DUPLICATE KEY UPDATE  ";

        if (($key = array_search($primary, $fields)) !== false) {
            unset($fields[$key]);
        }

        foreach ($fields as $field) {
            $sqlRow .= " `" . $field . "`='" . $values[$field] . "',";
        }
        $sqlRow = substr($sqlRow, 0, strlen($sqlRow) - 1);
        $sqlRow .= ";";
        $sqlRow = $this->removeNewLines($sqlRow);
        $this->writeIntoFile($sqlRow, $table);
    }

    private function table_user_recipe_calculated()
    {
        $table = 'user_recipe_calculated';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (`id`,
	`user_id`,
	`recipe_id`,
	`custom_recipe_id`,
	`ingestion_id`,
	`invalid`,
	`recipe_data`,
	`created_at`,
	`updated_at`) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . intval($row->recipe_id) . ",
	" . intval($row->custom_recipe_id) . ",
	" . intval($row->ingestion_id) . ",
	" . intval($row->invalid) . ",
	" . json_encode($row->recipe_data) . ",
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`user_id`=" . intval($row->user_id) . ",
                    `recipe_id`=" . intval($row->recipe_id) . ",
                    `custom_recipe_id`=" . intval($row->custom_recipe_id) . ",
                    `ingestion_id`=" . intval($row->ingestion_id) . ",
                    `invalid`=" . intval($row->invalid) . ",
                    `recipe_data`=" . json_encode($row->recipe_data) . ",
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";

            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_user_recipe()
    {
        $table = 'user_recipe';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('recipe_id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
    `user_id`,
	`recipe_id`,
	`visible`,
	`created_at`,
	`updated_at`) VALUES (
	" . intval($row->user_id) . ",
	" . intval($row->recipe_id) . ",
	" . intval($row->visible) . ",
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`user_id`=" . intval($row->user_id) . ",
                    `recipe_id`=" . intval($row->recipe_id) . ",
                    `visible`=" . intval($row->visible) . ",                 
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_recipes_to_users()
    {
        $table = 'recipes_to_users';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('recipe_id')
            ->get();

        if (empty($items)) {
            return false;
        }

        // TODO:: @NickMost review this in case of challenge_id

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
    `user_id`,
`recipe_id`,
`custom_recipe_id`,
`original_recipe_id`,
`challenge_id`,
`meal_date`,
`meal_time`,
`ingestion_id`,
`cooked`,
`eat_out`,
`flexmeal_id`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->user_id) . ",
	" . (is_null($row->recipe_id) ? ("NULL") : intval($row->recipe_id)) . ",
	" . (is_null($row->custom_recipe_id) ? ("NULL") : intval($row->custom_recipe_id)) . ",
	" . (is_null($row->original_recipe_id) ? ("NULL") : intval($row->original_recipe_id)) . ",
	" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",
	" . (is_null($row->meal_date) ? ("NULL") : "'" . $row->meal_date . "'") . ",
	'" . $row->meal_time . "',
	" . intval($row->ingestion_id) . ",
	" . intval($row->cooked) . ",
	" . intval($row->eat_out) . ",
	" . (is_null($row->flexmeal_id) ? ("NULL") : intval($row->flexmeal_id)) . ",
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`user_id`=" . intval($row->user_id) . ",
                    `recipe_id`=" . (is_null($row->recipe_id) ? ("NULL") : intval($row->recipe_id)) . ",
                    `custom_recipe_id`=" . (is_null($row->custom_recipe_id) ? ("NULL") : intval($row->custom_recipe_id)) . ",                 
                    `original_recipe_id`=" . (is_null($row->original_recipe_id) ? ("NULL") : intval($row->original_recipe_id)) . ",                 
                    `challenge_id`=" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",                 
                               
                    `meal_date`=" . (is_null($row->meal_date) ? ("NULL") : "'" . $row->meal_date . "'") . ",
                    `meal_time`='" . $row->meal_time . "',
                    `ingestion_id`=" . intval($row->ingestion_id) . ",      
                    `cooked`=" . intval($row->cooked) . ",      
                    `eat_out`=" . intval($row->eat_out) . ",      
                    `flexmeal_id`=" . (is_null($row->flexmeal_id) ? ("NULL") : intval($row->flexmeal_id)) . ",      
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_chargebee_subscriptions()
    {
        $table = 'chargebee_subscriptions';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
    `id`,
    `user_id`,
`assigned_user_id`,
`data`,
`payment_method`,
`uuid`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . intval($row->assigned_user_id) . ",
	'" . $row->data . "',
	" . (is_null($row->payment_method) ? ("NULL") : "'" . $row->payment_method . "'") . ",
	'" . $row->uuid . "',
	
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",
                    `assigned_user_id`=" . intval($row->assigned_user_id) . ",    
                    `data`='" . $row->data . "',
                    `payment_method`=" . (is_null($row->payment_method) ? ("NULL") : "'" . $row->payment_method . "'") . ",
                    `uuid`='" . $row->uuid . "',
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_formulars()
    {
        $table = 'formulars';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`approved`,
`forced_visibility`,
`creator_id`,
`creation_method`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . intval($row->approved) . ",
	" . intval($row->forced_visibility) . ",
	" . (is_null($row->creator_id) ? ("NULL") : intval($row->creator_id)) . ",
	'" . $row->creation_method . "',
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",
                    `approved`=" . intval($row->approved) . ",    
                    `forced_visibility`=" . intval($row->forced_visibility) . ",    
                    `creator_id`=" . (is_null($row->creator_id) ? ("NULL") : intval($row->creator_id)) . ",    
                    `creation_method`='" . $row->creation_method . "',                
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_survey_answers()
    {
        $table = 'survey_answers';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`formular_id`,
`survey_question_id`,
`challenge_id`,
`answer`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . (is_null($row->formular_id) ? ("NULL") : intval($row->formular_id)) . ",
	" . intval($row->survey_question_id) . ",
	" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",
	'" . $row->answer . "',
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",
                    `formular_id`=" . (is_null($row->formular_id) ? ("NULL") : intval($row->formular_id)) . ",    
                    `survey_question_id`=" . intval($row->survey_question_id) . ",    
                    `challenge_id`=" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",    
                    `answer`='" . $row->answer . "',                
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_client_notes()
    {
        $table = 'client_notes';

        $items = DB::table($table)
            ->select('*')
            ->where('client_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`author_id`,
`client_id`,
`text`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->id) . ",
	" . (is_null($row->author_id) ? ("NULL") : intval($row->author_id)) . ",
	" . intval($row->client_id) . ",
	'" . $row->text . "',
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`author_id`=" . (is_null($row->author_id) ? ("NULL") : intval($row->author_id)) . ",
                    `client_id`=" . intval($row->client_id) . ",                       
                    `text`='" . $row->text . "',                
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_abo_challenges_users()
    {
        $table = 'course_users';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`course_id`,
`start_at`,
`ends_at`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . intval($row->course_id) . ",
	'" . $row->start_at . "',
	'" . $row->ends_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",
                    `course_id`=" . intval($row->course_id) . ",                                                           
                    `start_at`='" . $row->start_at . "',
                    `ends_at`='" . $row->ends_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_custom_recipes()
    {
        $table = 'custom_recipes';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`challenge_id`,
`ingestion_id`,
`recipe_id`,
`title`,
`error`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",
	" . intval($row->ingestion_id) . ",
	" . (is_null($row->recipe_id) ? ("NULL") : intval($row->recipe_id)) . ",
	'" . $row->title . "',
	" . intval($row->error) . ",
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",
                    `challenge_id`=" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",    
                    `ingestion_id`=" . intval($row->ingestion_id) . ",    
                    `recipe_id`=" . (is_null($row->recipe_id) ? ("NULL") : intval($row->recipe_id)) . ",    
                    `title`='" . $row->title . "',                
                    `error`=" . intval($row->error) . ",                
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_custom_recipe_categories()
    {
        $table = 'custom_recipe_categories';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`name`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	'" . $row->name . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",                    
                    `name`='" . $row->name . "'                    
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_user_bulk_exclusions()
    {
        // TODO:: remove duplicates
        $table = 'user_bulk_exclusions';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('allergy_id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`user_id`,
`allergy_id`
) VALUES (
	" . intval($row->user_id) . ",
	" . intval($row->allergy_id) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`user_id`=" . intval($row->user_id) . ",                    
                	`allergy_id`=" . intval($row->allergy_id) . "                  
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_user_excluded_ingredients()
    {
        // TODO:: remove duplicates
        $table = 'user_excluded_ingredients';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('ingredient_id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`user_id`,
`ingredient_id`
) VALUES (
	" . intval($row->user_id) . ",
	" . intval($row->ingredient_id) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`user_id`=" . intval($row->user_id) . ",                    
                	`ingredient_id`=" . intval($row->ingredient_id) . "                  
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_user_excluded_recipes()
    {
        // TODO:: remove duplicates
        $table = 'user_excluded_recipes';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('created_at')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`user_id`,
`recipe_id`,
`created_at`
) VALUES (
	" . intval($row->user_id) . ",
	" . intval($row->recipe_id) . ",
	'" . $row->created_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`user_id`=" . intval($row->user_id) . ",                    
                	`recipe_id`=" . intval($row->recipe_id) . ",                
                	`created_at`='" . $row->created_at . "'                
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_user_to_challenge()
    {
        $table = 'user_subscription';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`challenge_id`,
`ends_at`,
`active`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",
	" . (is_null($row->ends_at) ? ("NULL") : "'" . $row->ends_at . "'") . ",
	" . intval($row->active) . ",
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",
                    `challenge_id`=" . (is_null($row->challenge_id) ? ("NULL") : intval($row->challenge_id)) . ",       
                    `ends_at`=" . (is_null($row->ends_at) ? ("NULL") : "'" . $row->ends_at . "'") . ",                
                    `active`=" . intval($row->active) . ",                
                    `created_at`='" . $row->created_at . "',
                    `updated_at`='" . $row->updated_at . "'
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_favorites()
    {
        $table = 'favorites';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`recipe_id`,
`created_at`,
`updated_at`
) VALUES (
	" . intval($row->id) . ",
	" . intval($row->user_id) . ",
	" . intval($row->recipe_id) . ",
	'" . $row->created_at . "',
	'" . $row->updated_at . "'
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . intval($row->id) . ",
                	`user_id`=" . intval($row->user_id) . ",          
                    `recipe_id`=" . intval($row->recipe_id) . ",                
                    `created_at`=" . $this->valueDates($row->created_at) . ",
                    `updated_at`=" . $this->valueDates($row->updated_at) . "
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_diary_datas()
    {
        $table = 'diary_datas';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`weight`,
`waist`,
`upper_arm`,
`leg`,
`mood`,
`image_file_name`,
`image_file_size`,
`image_content_type`,
`image_updated_at`,
`created_at`,
`updated_at`
) VALUES (
	" . $this->valueInt($row->id) . ",
	" . $this->valueInt($row->user_id) . ",
	" . $this->valueNullableFloat($row->weight) . ",
	" . $this->valueNullableFloat($row->waist) . ",
	" . $this->valueNullableFloat($row->upper_arm) . ",
	" . $this->valueNullableFloat($row->leg) . ",
	" . $this->valueNullableInt($row->mood) . ",
	" . $this->valueNullableString($row->image_file_name) . ",
	" . $this->valueNullableInt($row->image_file_size) . ",
	" . $this->valueNullableString($row->image_content_type) . ",
	" . $this->valueDates($row->image_updated_at) . ",
	" . $this->valueDates($row->created_at) . ",
	" . $this->valueDates($row->updated_at) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . $this->valueInt($row->id) . ",
                	`user_id`=" . $this->valueInt($row->user_id) . ",          
                    `weight`=" . $this->valueNullableFloat($row->weight) . ",                
                    `waist`=" . $this->valueNullableFloat($row->waist) . ",                
                    `upper_arm`=" . $this->valueNullableFloat($row->upper_arm) . ",                
                    `leg`=" . $this->valueNullableFloat($row->leg) . ",                
                    `mood`=" . $this->valueNullableInt($row->mood) . ",                
                    `image_file_name`=" . $this->valueNullableString($row->image_file_name) . ",                
                    `image_file_size`=" . $this->valueNullableInt($row->image_file_size) . ",                
                    `image_content_type`=" . $this->valueNullableString($row->image_content_type) . ",                
                    `image_updated_at`=" . $this->valueDates($row->image_updated_at) . ",                
                    `created_at`=" . $this->valueDates($row->created_at) . ",
                    `updated_at`=" . $this->valueDates($row->updated_at) . "
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_flexmeal_lists()
    {
        $table = 'flexmeal_lists';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`name`,
`mealtime`,
`notes`,
`image_file_name`,
`image_file_size`,
`image_content_type`,
`image_updated_at`,
`created_at`,
`updated_at`
) VALUES (
	" . $this->valueInt($row->id) . ",
	" . $this->valueInt($row->user_id) . ",
	" . $this->valueNullableString($row->name) . ",
	" . $this->valueNullableString($row->mealtime) . ",
	" . $this->valueNullableString($row->notes) . ",	
	" . $this->valueNullableString($row->image_file_name) . ",
	" . $this->valueNullableInt($row->image_file_size) . ",
	" . $this->valueNullableString($row->image_content_type) . ",
	" . $this->valueDates($row->image_updated_at) . ",
	" . $this->valueDates($row->created_at) . ",
	" . $this->valueDates($row->updated_at) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . $this->valueInt($row->id) . ",
                	`user_id`=" . $this->valueInt($row->user_id) . ",            
                    `name`=" . $this->valueNullableString($row->name) . ",                
                    `mealtime`=" . $this->valueNullableString($row->mealtime) . ",                
                    `notes`=" . $this->valueNullableString($row->notes) . ",                                                           
                    `image_file_name`=" . $this->valueNullableString($row->image_file_name) . ",                
                    `image_file_size`=" . $this->valueNullableInt($row->image_file_size) . ",                
                    `image_content_type`=" . $this->valueNullableString($row->image_content_type) . ",                
                    `image_updated_at`=" . $this->valueDates($row->image_updated_at) . ",                
                    `created_at`=" . $this->valueDates($row->created_at) . ",
                    `updated_at`=" . $this->valueDates($row->updated_at) . "
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_flexmeal_to_users()
    {
        $lists = DB::table('flexmeal_lists')
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (empty($lists)) {
            return false;
        }

        $table = 'flexmeal_to_users';

        $items = DB::table($table)
            ->select('*')
            ->whereIn('list_id', $lists)
            ->orderBy('id')
            ->get();

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`list_id`,
`amount`,
`ingredient_id`,
`created_at`,
`updated_at`
) VALUES (
	" . $this->valueInt($row->id) . ",
	" . $this->valueInt($row->list_id) . ",
	" . $this->valueNullableFloat($row->amount) . ",
	" . $this->valueNullableInt($row->ingredient_id) . ",
	" . $this->valueDates($row->created_at) . ",
	" . $this->valueDates($row->updated_at) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . $this->valueInt($row->id) . ",
                	`list_id`=" . $this->valueInt($row->list_id) . ",            
                    `amount`=" . $this->valueNullableFloat($row->amount) . ",                
                    `ingredient_id`=" . $this->valueNullableInt($row->ingredient_id) . ",                                                
                    `created_at`=" . $this->valueDates($row->created_at) . ",
                    `updated_at`=" . $this->valueDates($row->updated_at) . "
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_wallets_v2()
    {
        $table = 'wallets_v2';

        $items = DB::table($table)
            ->select('*')
            ->where('holder_id', $this->userId)
            ->where('holder_type', 'App\Models\User')
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`holder_type`,
`holder_id`,
`name`,
`slug`,
`uuid`,
`description`,
`meta`,
`balance`,
`decimal_places`,
`created_at`,
`updated_at`
) VALUES (
	" . $this->valueInt($row->id) . ",
	" . $this->valueString($row->holder_type) . ",
	" . $this->valueInt($row->holder_id) . ",
	" . $this->valueString($row->name) . ",
	" . $this->valueString($row->slug) . ",
	" . $this->valueString($row->uuid) . ",
	" . $this->valueNullableString($row->description) . ",
	" . $this->valueNullableString($row->meta) . ",
	" . $this->valueNullableFloat($row->balance) . ",
	" . $this->valueInt($row->decimal_places) . ",
	" . $this->valueDates($row->created_at) . ",
	" . $this->valueDates($row->updated_at) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . $this->valueInt($row->id) . ",
                	`holder_type`=" . $this->valueString($row->holder_type) . ",            
                    `holder_id`=" . $this->valueInt($row->holder_id) . ",                
                    `name`=" . $this->valueInt($row->name) . ",                                                
                    `slug`=" . $this->valueInt($row->slug) . ",                                                
                    `uuid`=" . $this->valueInt($row->uuid) . ",                                                
                    `description`=" . $this->valueNullableString($row->description) . ",                                                
                    `meta`=" . $this->valueNullableString($row->meta) . ",                                                
                    `balance`=" . $this->valueNullableFloat($row->balance) . ",                                                
                    `decimal_places`=" . $this->valueInt($row->decimal_places) . ",                                                
                    `created_at`=" . $this->valueDates($row->created_at) . ",
                    `updated_at`=" . $this->valueDates($row->updated_at) . "
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_transactions()
    {
        $walletsId = DB::table('wallets_v2')
            ->select('*')
            ->where('holder_id', $this->userId)
            ->where('holder_type', 'App\Models\User')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();


        if (empty($walletsId)) {
            return false;
        }
        $table = 'transactions';

        $items = DB::table($table)
            ->select('*')
            ->whereIn('wallet_id', $walletsId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`payable_type`,
`payable_id`,
`wallet_id`,
`type`,
`amount`,
`confirmed`,
`meta`,
`uuid`,
`created_at`,
`updated_at`
) VALUES (
	" . $this->valueInt($row->id) . ",
	" . $this->valueString($row->payable_type) . ",
	" . $this->valueInt($row->payable_id) . ",
	" . $this->valueInt($row->wallet_id) . ",
	" . $this->valueString($row->type) . ",
	" . $this->valueFloat($row->amount) . ",
	" . $this->valueInt($row->confirmed) . ",
	" . $this->valueNullableString($row->meta) . ",
	" . $this->valueString($row->uuid) . ",
	" . $this->valueDates($row->created_at) . ",
	" . $this->valueDates($row->updated_at) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . $this->valueInt($row->id) . ",
                	`payable_type`=" . $this->valueString($row->payable_type) . ",            
                    `payable_id`=" . $this->valueInt($row->payable_id) . ",                
                    `wallet_id`=" . $this->valueInt($row->wallet_id) . ",                                                
                    `type`=" . $this->valueString($row->type) . ",                                                
                    `amount`=" . $this->valueFloat($row->amount) . ",                                                
                    `confirmed`=" . $this->valueInt($row->confirmed) . ",                                                
                    `meta`=" . $this->valueNullableString($row->meta) . ",                                                
                    `uuid`=" . $this->valueString($row->uuid) . ",                                                                                                         
                    `created_at`=" . $this->valueDates($row->created_at) . ",
                    `updated_at`=" . $this->valueDates($row->updated_at) . "
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

    private function table_posts()
    {
        $table = 'posts';

        $items = DB::table($table)
            ->select('*')
            ->where('user_id', $this->userId)
            ->orderBy('id')
            ->get();

        if (empty($items)) {
            return false;
        }

        foreach ($items as $row) {
            $sqlRow = "INSERT INTO $table (
`id`,
`user_id`,
`diary_data_id`,
`content`,
`mood`,
`image_file_name`,
`image_file_size`,
`image_content_type`,
`image_updated_at`,
`created_at`,
`updated_at`
) VALUES (
	" . $this->valueInt($row->id) . ",
	" . $this->valueInt($row->user_id) . ",
	" . $this->valueInt($row->diary_data_id) . ",
	" . $this->valueString($row->content) . ",
	" . $this->valueInt($row->mood) . ",	
	" . $this->valueNullableString($row->image_file_name) . ",
	" . $this->valueNullableInt($row->image_file_size) . ",
	" . $this->valueNullableString($row->image_content_type) . ",
	" . $this->valueDates($row->image_updated_at) . ",
	" . $this->valueDates($row->created_at) . ",
	" . $this->valueDates($row->updated_at) . "
	) ON DUPLICATE KEY UPDATE  ";

            $sqlRow .= "
                	`id`=" . $this->valueInt($row->id) . ",
                	`user_id`=" . $this->valueInt($row->user_id) . ",            
                    `diary_data_id`=" . $this->valueInt($row->diary_data_id) . ",                
                    `content`=" . $this->valueString($row->content) . ",                
                    `mood`=" . $this->valueInt($row->mood) . ",                                                           
                    `image_file_name`=" . $this->valueNullableString($row->image_file_name) . ",                
                    `image_file_size`=" . $this->valueNullableInt($row->image_file_size) . ",                
                    `image_content_type`=" . $this->valueNullableString($row->image_content_type) . ",                
                    `image_updated_at`=" . $this->valueDates($row->image_updated_at) . ",                
                    `created_at`=" . $this->valueDates($row->created_at) . ",
                    `updated_at`=" . $this->valueDates($row->updated_at) . "
                    ";
            $sqlRow .= ";";
            $sqlRow = $this->removeNewLines($sqlRow);
            $this->writeIntoFile($sqlRow, $table);
        }
    }

}
