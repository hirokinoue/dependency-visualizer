<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

final class Logger
{
    private static ?MonologLogger $logger = null;

    public static function initialize(bool $enable): void
    {
        self::$logger = new MonologLogger('dependency visualizer');
        if ($enable) {
            $handler = new StreamHandler(getcwd() . '/app.log');
            $handler->setFormatter(new LineFormatter(null, null, true));
            self::$logger->pushHandler($handler);
        } else {
            self::$logger->pushHandler(new NullHandler());
        }
    }

    /**
     * @param mixed[] $context
     */
    public static function info(string $message, array $context = []): void
    {
        if (self::$logger === null) {
            return;
        }
        self::$logger->info($message, $context);
    }

    /**
     * @param mixed[] $context
     */
    public static function error(string $message, array $context = []): void
    {
        if (self::$logger === null) {
            return;
        }
        self::$logger->error($message, $context);
    }
}
