<?php

namespace App\Logging;

use App\Exceptions\IncompleteTelegramConfig;
use App\Logging\Formatter\TelegramBotLoggingFormatter;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Logger;

/**
 * Telegram logger factory.
 *
 * @package App\Logging
 */
final class TelegramLoggerFactory
{
    /**
     * @throws \App\Exceptions\IncompleteTelegramConfig
     * @throws \Monolog\Handler\MissingExtensionException
     */
    public function __invoke(array $config): Logger
    {
        $apiKey     = $config['api_key'];
        $channel    = $config['channel'];
        $level      = $config['level'] ?? 'debug';
        $dateFormat = $config['date_format'] ?? 'Y/m/d H:i:s';

        if (empty($apiKey)) {
            throw new IncompleteTelegramConfig('Missing api_key in config/logging.php');
        }

        if (empty($channel)) {
            throw new IncompleteTelegramConfig('Missing channel in config/logging.php');
        }

        $logHandler = new TelegramBotHandler(
            $apiKey,
            $channel,
            $level,
            true,
            'HTML',
            true,
            true,
            false,
            true
        );

        $logger = new Logger('Telegram');

        $logHandler->setFormatter(new TelegramBotLoggingFormatter($dateFormat));
        $logger->pushHandler($logHandler);

        return $logger;
    }
}
