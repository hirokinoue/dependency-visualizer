<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\ClassLikeNodeFinder;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt;
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
        $code = file_get_contents(__DIR__ . '/data/Foo.php');
        $parser = (new ParserFactory())->createForHostVersion();
        /** @var Stmt[] $stmts */
        $stmts = $parser->parse($code);
        $sut = ClassLikeNodeFinder::create($stmts);
        if ($sut === null) {
            $this->fail('クラス宣言のノードが取得できませんでした。');
        }

        // when
        $className = $sut->classLikeName();

        // then
        $this->assertSame('\Hirokinoue\DependencyVisualizer\Tests\data\Foo', $className);
    }
}
