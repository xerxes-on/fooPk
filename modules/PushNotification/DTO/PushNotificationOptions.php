<?php

declare(strict_types=1);

namespace Modules\PushNotification\DTO;

use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Modules\Course\Enums\UserCourseStatus;
use Modules\PushNotification\Enums\UserGroupOptionEnum;
use Modules\PushNotification\Exceptions\NoUsersForSelectedCriteria;

/**
 * Options for push notification job.
 *
 * @package Modules\PushNotification\DTO
*/
final class PushNotificationOptions
{
    private string $language;

    /**
     * @throws NoUsersForSelectedCriteria
     */
    public function __construct(
        ?string               $language = null,
        private array         $usersId = [],
        private readonly ?int $courseID = null,
        private readonly ?int $courseStatus = null,
        private readonly bool $isSilent = false
    ) {
        $this->language = $language ?? UserGroupOptionEnum::DEFAULT->value;
        $this->addUsersFromCourse();
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getCourseID(): ?int
    {
        return $this->courseID;
    }

    public function getCourseStatus(): ?int
    {
        return $this->courseStatus;
    }

    public function getUsersId(): array
    {
        return $this->usersId;
    }

    public function isSilent(): bool
    {
        return $this->isSilent;
    }

    public function toArray(): array
    {
        $return = [
            UserGroupOptionEnum::NAME => $this->language,
        ];

        if ($this->courseID && $this->courseStatus) {
            $return['course'] = "#{$this->courseID} as ". UserCourseStatus::tryFrom($this->courseStatus)?->ucName() ?? 'Unknown';
        }

        return $return;
    }

    /**
     * @throws NoUsersForSelectedCriteria
     */
    private function addUsersFromCourse(): void
    {
        if (is_null($this->courseID) || is_null($this->courseStatus)) {
            return;
        }

        $result = $this->getUsersFromCourseRequest();

        if (empty($result)) {
            throw new NoUsersForSelectedCriteria(trans('PushNotification::admin.no_users_found'));
        }

        $this->usersId = array_merge($this->usersId, $result);
    }

    private function getUsersFromCourseRequest(): array
    {
        $havingCondition = match (UserCourseStatus::tryFrom($this->courseStatus)) {
            UserCourseStatus::IN_PROGRESS   => 'active_days < duration and active_days != 0',
            UserCourseStatus::FINISHED      => 'active_days > duration',
            UserCourseStatus::NOT_STARTED   => 'active_days = 0',
            UserCourseStatus::NOT_PURCHASED => false,
            default                         => null,
        };
        if (is_null($havingCondition)) {
            return [];
        }
        if (false === $havingCondition) {
            return DB::table('users')
                ->leftJoin('course_users', function (JoinClause $join) {
                    $join->on('users.id', '=', 'course_users.user_id')
                        ->where('course_users.course_id', '=', $this->courseID);
                })
                ->where('users.status', '1')
                ->whereNull('course_users.user_id')
                ->pluck('users.id')
                ->toArray();
        }

        $now = Carbon::now();
        return DB::table('course_users')
            ->distinct()
            ->where('course_id', $this->courseID)
            ->select(['user_id'])
            ->selectSub(
                DB::table('courses')
                    ->select('duration')
                    ->where('id', $this->courseID),
                'duration'
            )
            ->selectRaw(
                <<<'EOT'
CASE
    WHEN start_at < ? AND ends_at > ? THEN DATEDIFF(?, start_at) + 1
    WHEN ends_at < ? THEN DATEDIFF(?, start_at)
    ELSE 0
END AS active_days
EOT
                ,
                [$now, $now, $now, $now, $now]
            )
            ->havingRaw($havingCondition)
            ->get()
            ->pluck('user_id')
            ->toArray();
    }
}
