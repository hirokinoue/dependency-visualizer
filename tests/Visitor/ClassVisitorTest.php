<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests\Visitor;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeNodeFinder;
use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeWrapper;
use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLoader;
use Hirokinoue\DependencyVisualizer\Config\Config;
use Hirokinoue\DependencyVisualizer\DiagramUnit;
use Hirokinoue\DependencyVisualizer\Exporter\StringExporter;
use Hirokinoue\DependencyVisualizer\Visitor\ClassVisitor;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class ClassVisitorTest extends TestCase
{
    protected function setUp(): void
    {
        ClassLoader::resetLoadedClasses();
        DiagramUnit::resetVisitedClasses();
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testユーザ定義クラスの名前と内容_定義済みクラスの名前が取得できる_それ以外取得しないこと(): void
    {
        // given
        $stmts = $this->parse(__DIR__ . '/../data/root.php');
        $diagramUnit = new DiagramUnit(
            'root',
            true,
            null
        );
        $nodeTraverser = $this->setUpTraverser($diagramUnit);
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

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testClassVisitorに登録されたことがあるクラスは再びトラバースしないこと(): void
    {
        // given
        $stmts = $this->parse(__DIR__ . '/../data/Visitor/VisitedClass/A.php');
        $classLike = ClassLikeNodeFinder::find($stmts);
        $diagramUnit = new DiagramUnit(
            '\Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass\A',
            false,
            $classLike
        );
        $nodeTraverser = $this->setUpTraverser($diagramUnit);
        $expected = <<<RESULT
\Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass\A
  \Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass\B
    \Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass\A
  \Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass\C
    \Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass\B

RESULT;

        // when
        $nodeTraverser->traverse($stmts);

        $stringExporter = new StringExporter();
        $result = $stringExporter->export($diagramUnit);

        // then
        $this->assertSame($expected, $result);
        // A, B, Cの3つがトラバース（A, Bは1度だけトラバース）されることを期待。
        $this->assertSame(3, DiagramUnit::countVisitedClasses());
    }

    /**
     * @dataProvider data指定された名前空間を解析しないこと
     * @noinspection NonAsciiCharacters
     */
    public function test指定された名前空間を解析しないこと(string $path): void
    {
        // given
        Config::initialize($path);
        $sut = new ClassVisitor(new DiagramUnit(
            '\Hirokinoue\DependencyVisualizer\Tests\data\Foo',
            false,
            new ClassLikeWrapper(new Class_(new Identifier('Foo')))
        ));

        // when
        $sut->enterNode(new FullyQualified('Hirokinoue\DependencyVisualizer\Tests\data\Qux'));

        // then
        // QuxがカウントされずFooの1回のみがカウントされることを期待。
        $this->assertSame(1, DiagramUnit::countVisitedClasses());
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function data指定された名前空間を解析しないこと(): \Generator
    {
        yield '名前空間を完全修飾名で指定する' => [
            __DIR__ . '/../data/Visitor/Config/0',
        ];
        yield '名前空間を修飾名で指定する' => [
            __DIR__ . '/../data/Visitor/Config/1',
        ];
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function test指定された深さ以上解析しないこと(): void
    {
        // given
        Config::initialize(__DIR__ . '/../data/Visitor/Config/maxDepth');
        $sut = new ClassVisitor(new DiagramUnit(
            '\Foo',
            false,
            new ClassLikeWrapper(new Class_(new Identifier('Foo'))),
            1
        ));

        // when
        $sut->enterNode(new FullyQualified('Hirokinoue\DependencyVisualizer\Tests\data\Baz'));

        // then
        $this->assertSame(1, DiagramUnit::countVisitedClasses());
    }

    /**
     * @return Stmt[]
     */
    private function parse(string $path): array
    {
        /** @var string $code */
        $code = \file_get_contents($path);
        $parser = (new ParserFactory())->createForHostVersion();
        $stmts = $parser->parse($code);
        return $stmts ?? [];
    }

    private function setUpTraverser(DiagramUnit $diagramUnit): NodeTraverser
    {
        $sut = new ClassVisitor($diagramUnit);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor($sut);
        return $nodeTraverser;
    }
}
