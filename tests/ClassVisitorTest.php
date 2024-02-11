<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\ClassVisitor;
use Hirokinoue\DependencyVisualizer\DiagramUnit;
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
        /** @var string $code */
        $code = file_get_contents(__DIR__ . '/data/RootClass.php');
        $parser = (new ParserFactory())->createForHostVersion();
        /** @var Stmt[] $stmts */
        $stmts = $parser->parse($code);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $namespace = '\Hirokinoue\DependencyVisualizer\Tests\data';
        $nodeTraverser->addVisitor(new ClassVisitor($diagramUnit = new DiagramUnit($namespace . '\RootClass', [$namespace . '\RootClass'])));

        $expected = new DiagramUnit($namespace . '\RootClass', [$namespace . '\RootClass']);
        $baz = new DiagramUnit($namespace . '\Baz', [$namespace . '\RootClass', $namespace . '\Baz']);
        $baz->push(new DiagramUnit($namespace . '\Qux', [$namespace . '\RootClass', $namespace . '\Baz', $namespace . '\Qux']));
        $expected->push(new DiagramUnit('\Error', [$namespace . '\RootClass', '\Error']));
        $expected->push(new DiagramUnit($namespace . '\Bar', [$namespace . '\RootClass', $namespace . '\Bar']));
        $expected->push($baz);
        $expected->push(new DiagramUnit('\stdClass', [$namespace . '\RootClass', '\stdClass']));

        // when
        $nodeTraverser->traverse($stmts);

        // then
        $this->assertEquals($expected, $diagramUnit, print_r($diagramUnit, true));
    }
}
