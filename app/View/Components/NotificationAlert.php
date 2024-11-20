<?php

namespace App\View\Components;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Notification Alert component.
 *
 * @package App\View\Components
 */
final class NotificationAlert extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?string $message = null, public array $config = [])
    {
        $this->config = array_merge(
            [
                'container'        => false,
                'closable'         => false,
                'dismiss_duration' => 0,
                'dismiss_id'       => 'alert',
                'type'             => 'warning',
                'rowWrapper'       => true,
            ],
            $config
        );

        // Prevent rendering in case Alert should be hidden for some time
        if ($this->config['closable'] && $this->config['dismiss_duration'] > 0) {
            try {
                $lastSeen = intval(strip_tags(trim($_COOKIE["alert_{$this->config['dismiss_id']}"] ?? '')));

                if ($lastSeen > 0 && Carbon::parse($lastSeen)->diffInSeconds(Carbon::now()) < $this->config['dismiss_duration']) {
                    $this->message = null;
                }
            } catch (InvalidFormatException $e) {
                logError($e);
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.notification-alert');
    }
}
