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
        $nodeTraverser->addVisitor(new ClassVisitor($diagramUnit = new DiagramUnit('RootClass')));

        $expected = new DiagramUnit('RootClass');
        $baz = new DiagramUnit('\Hirokinoue\DependencyVisualizer\Tests\data\Baz');
        $baz->push(new DiagramUnit('\Hirokinoue\DependencyVisualizer\Tests\data\Qux'));
        $expected->push(new DiagramUnit('\Error'));
        $expected->push(new DiagramUnit('\Hirokinoue\DependencyVisualizer\Tests\data\Bar'));
        $expected->push($baz);
        $expected->push(new DiagramUnit('\stdClass'));

        // when
        $nodeTraverser->traverse($stmts);

        // then
        $this->assertEquals($expected, $diagramUnit, print_r($diagramUnit, true));
    }
}
