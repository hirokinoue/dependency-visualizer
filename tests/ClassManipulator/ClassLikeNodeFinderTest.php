<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests\ClassManipulator;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeNodeFinder;
use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeWrapper;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class ClassLikeNodeFinderTest extends TestCase
{
    /**
     * @noinspection NonAsciiCharacters
     */
    public function testクラス宣言のノードが取得できること(): void
    {
        // given
        /** @var string $code */
        $code = \file_get_contents(__DIR__ . '/../data/Foo.php');
        $parser = (new ParserFactory())->createForHostVersion();
        /** @var Stmt[] $stmts */
        $stmts = $parser->parse($code);

        // when
        $sut = ClassLikeNodeFinder::find($stmts);

        // then
        if ($sut === null) {
            $this->fail('クラス宣言のノードが取得できませんでした。');
        }
        $this->assertTrue($sut instanceof ClassLikeWrapper);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testクラス宣言のノードがないときnullを返すこと(): void
    {
        // given
        /** @var string $code */
        $code = \file_get_contents(__DIR__ . '/../data/foo.php');
        $parser = (new ParserFactory())->createForHostVersion();
        /** @var Stmt[] $stmts */
        $stmts = $parser->parse($code);

        // when
        $sut = ClassLikeNodeFinder::find($stmts);

        // then
        $this->assertNull($sut);
    }
}
