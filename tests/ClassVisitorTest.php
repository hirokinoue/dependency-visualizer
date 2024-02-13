<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\ClassVisitor;
use Hirokinoue\DependencyVisualizer\DiagramUnit;
use Hirokinoue\DependencyVisualizer\StringExporter;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class ClassVisitorTest extends TestCase
{
    /**
     * @noinspection NonAsciiCharacters
     */
    public function testユーザ定義クラスの名前と内容_定義済みクラスの名前が取得できる_それ以外取得しないこと(): void
    {
        // given
        DiagramUnit::resetVisitedClasses();
        /** @var string $code */
        $code = file_get_contents(__DIR__ . '/data/root.php');
        $parser = (new ParserFactory())->createForHostVersion();
        /** @var Stmt[] $stmts */
        $stmts = $parser->parse($code);
        $sut = new ClassVisitor($diagramUnit = new DiagramUnit('root', ['root']));
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor($sut);

        $expected = <<<RESULT
root
  \Hirokinoue\DependencyVisualizer\Tests\data\Bar
  \Hirokinoue\DependencyVisualizer\Tests\data\Baz
    \Hirokinoue\DependencyVisualizer\Tests\data\Qux
  \stdClass

RESULT;

        // when
        $nodeTraverser->traverse($stmts);

        $stringExporter = new StringExporter();
        $result = $stringExporter->export($diagramUnit);

        // then
        $this->assertSame($expected, $result);
    }
}
