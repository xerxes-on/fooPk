<?php

namespace App\Logging\Formatter;

use Illuminate\Support\Str;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;
use Monolog\Utils;

/**
 * Class TelegramBotLoggingFormatter
 *
 * @package App\Logging\Formatter
 */
final class TelegramBotLoggingFormatter extends NormalizerFormatter
{
    /**
     * The maximum number of characters allowed in a message according to the Telegram api documentation.
     *
     * @var int
     */
    private const MAX_MESSAGE_LENGTH = 4096;

    /**
     * Translation table for Telegram special characters.
     */
    private array $translationTable = [
        '"' => '`',
        "&" => '&amp;',
        "'" => '`',
    ];

    /**
     * Formats a set of log records.
     *
     * @param array<LogRecord> $records A set of records to format
     * @return string            The formatted set of records
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    /**
     * Formats a log record.
     *
     * @param LogRecord $record A record to format
     * @return string     The formatted record
     */
    public function format(LogRecord $record)
    {
        $output = "<b>{$record->level->getName()}</b>" . $this->addLineBreak();

        $output .= "<b>Time</b>: <i>{$this->formatDate($record->datetime)}</i>" . $this->addLineBreak();
        $output .= "<b>Channel</b>: <i>$record->channel</i>" . $this->addLineBreak();
        $output .= '<b>Message</b>:' . $this->addLineBreak();
        $output .= '<pre>' . $this->convertToString($record->message) . '</pre>';
        $output .= $this->addLineBreak();

        if ($record->context !== []) {
            $embeddedTable = '<pre>';
            foreach ($record->context as $key => $value) {
                $embeddedTable .= "$key: {$this->convertToString($value)}{$this->addLineBreak()}";
            }
            $embeddedTable .= '</pre>';
            $output .= '<b>Context</b>:' . $this->addLineBreak() . $embeddedTable . $this->addLineBreak();
        }
        if ($record->extra !== []) {
            $embeddedTable = '<pre>';
            foreach ($record->context as $key => $value) {
                $embeddedTable .= "$key: {$this->convertToString($value)}{$this->addLineBreak()}";
            }
            $embeddedTable .= '</pre>';
            $output .= '<b>Extra</b>: ' . $this->addLineBreak() . $embeddedTable . $this->addLineBreak();
        }

        return $this->cropLongMessage($output);
    }

    /**
     * Add a line break to the message.
     *
     * @return string
     */
    private function addLineBreak(): string
    {
        return PHP_EOL . "\r";
    }

    /**
     * Convert the data to a string.
     *
     * @param mixed $data
     *
     * @return string
     */
    private function convertToString(mixed $data): string
    {
        if (null === $data || is_scalar($data)) {
            return Str::replace(array_keys($this->translationTable), array_values($this->translationTable), (string)$data);
        }

        $data = $this->normalize($data);

        return Str::replace(
            array_keys($this->translationTable),
            array_values($this->translationTable),
            Utils::jsonEncode($data, JSON_PRETTY_PRINT | Utils::DEFAULT_JSON_FLAGS, true)
        );
    }

    /**
     * Handle a message that is too long.
     */
    private function cropLongMessage(string $message): string
    {
        $truncatedMarker = "...</pre>{$this->addLineBreak()}<i>Message truncated</i>";
        if (strlen($message) > self::MAX_MESSAGE_LENGTH) {
            return Utils::substr($message, 0, self::MAX_MESSAGE_LENGTH - strlen($truncatedMarker)) . $truncatedMarker;
        }

        return $message;
    }
}
