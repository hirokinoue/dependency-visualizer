<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\DependencyVisualizer;
use Hirokinoue\DependencyVisualizer\StringExporter;
use PHPUnit\Framework\TestCase;

final class DependencyVisualizerTest extends TestCase
{
    /**
     * @dataProvider data分析結果をテキスト形式で出力できること
     * @noinspection NonAsciiCharacters
     */
    public function test分析結果をテキスト形式で出力できること(string $path, string $expected): void
    {
        // given
        $sut = DependencyVisualizer::create($path);

        // when
        $stringExporter = new StringExporter();
        $result = $stringExporter->export($sut->analyze());

        // then
        $this->assertSame($expected, $result);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, string>>
     */
    public function data分析結果をテキスト形式で出力できること(): array
    {
        $rootIsClass = <<<RESULT
\Hirokinoue\DependencyVisualizer\Tests\data\Foo
  \Hirokinoue\DependencyVisualizer\Tests\data\Bar
  \Hirokinoue\DependencyVisualizer\Tests\data\Baz
    \Hirokinoue\DependencyVisualizer\Tests\data\Qux

RESULT;
        $rootIsNotClass = <<<RESULT
root
  \Hirokinoue\DependencyVisualizer\Tests\data\Bar
  \Hirokinoue\DependencyVisualizer\Tests\data\Baz
    \Hirokinoue\DependencyVisualizer\Tests\data\Qux

RESULT;
        $infiniteLoop = <<<RESULT
\Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop\A
  \Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop\A
  \Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop\B
    \Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop\A
    \Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop\C
      \Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop\A

RESULT;
        return [
            '始点がクラスの時ルートがクラス名' => [
                __DIR__ . '/data/Foo.php',
                $rootIsClass,
            ],
            '始点がクラスではない時ルートがroot' => [
                __DIR__ . '/data/foo.php',
                $rootIsNotClass,
            ],
            '循環依存があっても無限ループせず解析が完了する' => [
                __DIR__ . '/data/InfiniteLoop/A.php',
                $infiniteLoop,
            ],
        ];
    }
}
