<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLoader;
use Hirokinoue\DependencyVisualizer\DependencyVisualizer;
use Hirokinoue\DependencyVisualizer\DiagramUnit;
use Hirokinoue\DependencyVisualizer\Exporter\StringExporter;
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
        ClassLoader::resetLoadedClasses();
        DiagramUnit::resetVisitedClasses();
        $sut = DependencyVisualizer::create($path);

        // when
        $stringExporter = new StringExporter();
        $result = $stringExporter->export($sut->analyze());

        // then
        $this->assertSame($expected, $result);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function data分析結果をテキスト形式で出力できること(): \Generator
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
        $performanceEnhancement = <<<RESULT
\Hirokinoue\DependencyVisualizer\Tests\data\RedundantDependency\A
  \Hirokinoue\DependencyVisualizer\Tests\data\RedundantDependency\B
  \Hirokinoue\DependencyVisualizer\Tests\data\RedundantDependency\C

RESULT;
        yield '始点がクラスの時ルートがクラス名' => [
            __DIR__ . '/data/Foo.php',
            $rootIsClass,
        ];
        yield '始点がクラスではない時ルートがroot' => [
            __DIR__ . '/data/foo.php',
            $rootIsNotClass,
        ];
        yield '循環依存があっても無限ループせず解析が完了する' => [
            __DIR__ . '/data/InfiniteLoop/A.php',
            $infiniteLoop,
        ];
        yield '同じクラスを複数回使用する場合1つだけ図示する' => [
            __DIR__ . '/data/RedundantDependency/A.php',
            $performanceEnhancement,
        ];
    }
}
