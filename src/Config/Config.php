<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Config;

class Config
{
    private static string $memoryLimit = '1024M';
    private static int $maxDepth = 5;
    /** @var string[] */
    private static array $endOfAnalysis = [];
    /** @var string[] */
    private static array $excludeFromAnalysis = [];
    /** @var string[] */
    private static array $excludeFilePath = [];

    public static function initialize(string $baseDir): void
    {
        $config = [];
        foreach (['config.php', 'config.php.dist', 'config.dist.php'] as $discoverableConfigName) {
            $discoverableConfigFile = $baseDir . DIRECTORY_SEPARATOR . $discoverableConfigName;
            if (\is_file($discoverableConfigFile)) {
                $config = require $discoverableConfigFile;
                if (!\is_array($config)) {
                    throw new \InvalidArgumentException('Config file format is invalid: ' . $discoverableConfigFile);
                }
            }
        }
        self::$memoryLimit = $config['memoryLimit'] ?? '1024M';
        self::$maxDepth = $config['maxDepth'] ?? 5;
        self::$endOfAnalysis = $config['endOfAnalysis'] ?? [];
        self::$excludeFromAnalysis = $config['excludeFromAnalysis'] ?? [];
        self::$excludeFilePath = $config['excludeFilePath'] ?? [];
    }

    public static function memoryLimit(): string
    {
        return self::$memoryLimit;
    }

    public static function maxDepth(): int
    {
        return self::$maxDepth;
    }

    /**
     * @return string[]
     */
    public static function endOfAnalysis(): array
    {
        return self::$endOfAnalysis;
    }

    /**
     * @return string[]
     */
    public static function excludeFromAnalysis(): array
    {
        return self::$excludeFromAnalysis;
    }

    /**
     * @return string[]
     */
    public static function excludeFilePath(): array
    {
        return self::$excludeFilePath;
    }
}
