<?php

namespace Modules\Chargebee\Jobs;

use App\Http\Traits\CanGetProperty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Chargebee\Services\ChargebeeService;

/**
 * Class ProcessChargebeeWebhook
 *
 * @package Modules\Chargebee\Jobs
 */
class ProcessChargebeeWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CanGetProperty;


    private $webhookData;

    /**
     * Create a new job instance.
     * Only one param is passed into this instance, other should be invoked manually
     */
    public function __construct($webhookData)
    {
        $this->webhookData = $webhookData;
        $this->onQueue('chargebee');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app(ChargebeeService::class)->handleWebhook($this->webhookData);
    }
}
