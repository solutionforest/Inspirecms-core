<?php

namespace SolutionForest\InspireCms\Exports;

class ExportResult
{
    public function __construct(
        public ExportStatus $status,
        public ?string $filename,
        /**
         * @var null | string | array | \Exception
         */
        public $message = null,
    ) {}

    /**
     * @param  null | string | array  $message
     * @return ExportResult
     */
    public static function completed(string $filename, $message = null)
    {
        return new static(ExportStatus::Completed, $filename, $message);
    }

    /**
     * @param  null | string | array | \Exception  $message
     * @return ExportResult
     */
    public static function failed($message)
    {
        return new static(ExportStatus::Failed, null, $message);
    }

    /**
     * @param  array  $messages  Processing messages
     * @return ExportResult
     */
    public static function paused($messages)
    {
        return new static(ExportStatus::Paused, null, $messages);
    }
}
