<?php

namespace Modules\Internal\Console\Commands;

use App\Repositories\Dictionaries;
use Illuminate\Console\Command;

/**
 * Generate translations array
 *
 * @internal
 *
 * @package App\Console\Commands\Internal
 */
final class TranslationsCooker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internal_staging_translations_cooker {lang? : The lang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate translations array';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $lang           = trim(strtolower($this->argument('lang') ?? 'de'));
        $dictionaryRepo = new Dictionaries();

        if (empty($lang)) {
            return Command::FAILURE;
        }

        $appTranslations = $dictionaryRepo->get('mobile_app', $lang);
        ksort($appTranslations);

        $tmpDir = public_path() . "/tmp/";
        #get imported file which you want and read
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir);
        }

        if (($open = fopen($tmpDir . $lang . ".txt", "w")) !== false) {
            fwrite($open, var_export($appTranslations, true));
            fclose($open);
            $url = config('app.url') . '/tmp/' . $lang . ".txt";
            var_dump($url);
        }

        return Command::SUCCESS;
    }
}
