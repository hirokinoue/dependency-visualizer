<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests\Config;

use Hirokinoue\DependencyVisualizer\Config\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    /**
     * @noinspection NonAsciiCharacters
     */
    public function test指定したファイルから設定を取得できること(): void
    {
        // given
        Config::initialize(__DIR__ . '/../data/Config');

        // when
        $memoryLimit = Config::memoryLimit();
        $maxDepth = Config::maxDepth();
        $endOfAnalysis = Config::endOfAnalysis();
        $excludeFromAnalysis = Config::excludeFromAnalysis();
        $excludeFilePath = Config::excludeFilePath();

        // then
        $this->assertSame('Foo', $memoryLimit);
        $this->assertSame(5, $maxDepth);
        $this->assertSame(['Bar\\'], $endOfAnalysis);
        $this->assertSame(['Baz\\'], $excludeFromAnalysis);
        $this->assertSame(['src/Qux.php'], $excludeFilePath);
    }
}
