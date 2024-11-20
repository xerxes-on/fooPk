<?php

namespace App\Http\Traits\Queue;

use Illuminate\Support\Facades\Log;
use Modules\Internal\Models\AdminStorage;

trait HandleLastStartedJob
{
    private $relatedJobHash = false; // TODO: is it ok for private here? what is the type? bool or int?
    private $couldBeInterrupted = true;

    public static function staticGetRelatedJobId($type, $userId)
    {
        return AdminStorage::getAfterFormularChangeJobHash($type, $userId);
    }

    public function getRelatedJobId($type)
    {
        return self::staticGetRelatedJobId($type, $this->user->getKey());
    }

    public function initRelatedJobId($type)
    {
        if ($this->relatedJobHash !== false) {
            return $this->relatedJobHash;
        }
        return $this->relatedJobHash = AdminStorage::getAfterFormularChangeJobHash($type, $this->user->getKey());
    }

    public static function finishJob()
    {
        Log::info('Job finished, because exists newer');
        // TODO:: review how to finish job in static method....
    }

    public static function staticVerifyOrFinishJob($type, $userId, $relatedJobHash = null)
    {
        if (!empty($relatedJobHash) && $relatedJobHash != self::staticGetRelatedJobId($type, $userId)) {
            self::finishJob();
            return false;
        }
    }

    public function verifyOrFinishJob($type)
    {
        if ($this->relatedJobHash != $this->getRelatedJobId($type)) {
            //            self::finishJob();
            Log::info('Job finished, because exists newer');
            $this->job->delete();
            $this->delete();
            return false;
        }
        return $this->relatedJobHash;
    }
}
