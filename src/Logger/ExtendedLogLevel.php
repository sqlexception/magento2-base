<?php declare(strict_types=1);

namespace SqlException\Base\Logger;

use Psr\Log\LogLevel as PsrLogLevel;

/**
 * Extended log level class that adds an "OFF" level to disable logging.
 */
class ExtendedLogLevel extends PsrLogLevel
{
    public const DISABLED = 'disabled';
    public const XML_PATH_SUFFIX_LOG_LEVEL = 'log_level';

    public const XML_PATH_SUFFIX_LOG_FILE = 'log_file';

    public const XML_PATH_SUFFIX_LOG_CONTEXT = 'log_context';
}
