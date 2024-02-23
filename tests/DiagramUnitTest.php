<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeWrapper;
use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLoader;
use Hirokinoue\DependencyVisualizer\DiagramUnit;
use PhpParser\Modifiers;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;

final class DiagramUnitTest extends TestCase
{
    protected function setUp(): void
    {
        ClassLoader::resetLoadedClasses();
        DiagramUnit::resetVisitedClasses();
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testクラス名と名前空間が取得できること(): void
    {
        // given
        $sut = new DiagramUnit(
            '\Foo\Bar',
            ['\Foo\Bar'],
            false,
            null
        );


        // when
        $fullQualifiedClassName = $sut->fullyQualifiedClassName();
        $className = $sut->className();
        $namespace = $sut->namespace();

        // then
        $this->assertSame('\Foo\Bar', $fullQualifiedClassName);
        $this->assertSame('Bar', $className);
        $this->assertSame('\Foo', $namespace);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function test循環依存している時トラバースしないこと(): void
    {
        // given
        $sut = new DiagramUnit(
            '\Foo\Bar',
            ['\Foo\Bar'],
            false,
            null
        );
        $diagramUnit = new DiagramUnit(
            '\Foo\Baz',
            ['\Foo\Bar', '\Foo\Baz'],
            false,
            null
        );

        // when1
        $shouldStop = $sut->shouldStopTraverse();

        // then1
        $this->assertFalse($shouldStop);

        // when2
        $sut->push($diagramUnit);
        $diagramUnit->push($sut);
        $shouldStop = $sut->shouldStopTraverse();

        // then2
        $this->assertTrue($shouldStop);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function test解析したことのあるクラスを再び解析しない(): void
    {
        // given
        $sut = new DiagramUnit(
            '\Foo\Bar',
            ['\Foo\Bar'],
            false,
            null
        );

        // when1
        $shouldStop = $sut->hasBeenVisited();

        // then1
        $this->assertFalse($shouldStop);

        // when2
        $sut->registerVisitedClass();
        $shouldStop = $sut->hasBeenVisited();

        // then2
        $this->assertTrue($shouldStop);
    }

    /**
     * @dataProvider data解析結果のルートを見分けられること
     * @noinspection NonAsciiCharacters
     */
    public function test解析結果のルートを見分けられること(DiagramUnit $sut, bool $expectedClass, bool $expectedNonClass): void
    {
        // when
        $isClass = $sut->isClassLikeRoot();
        $isNonClass = $sut->isNonClassLikeRoot();

        // then
        $this->assertSame($expectedClass, $isClass);
        $this->assertSame($expectedNonClass, $isNonClass);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, DiagramUnit|bool>>
     */
    public function data解析結果のルートを見分けられること(): array
    {
        return [
            '解析結果がルートではない_クラスのファイルがない' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    null
                ),
                false,
                false,
            ],
            '解析結果がルートではない_クラスのファイルがある' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper(new Class_('Bar'))
                ),
                false,
                false,
            ],
            '解析結果がルート_クラスのファイルがない' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    true,
                    null
                ),
                false,
                true,
            ],
            '解析結果がルート_クラスのファイルがある' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    true,
                    new ClassLikeWrapper(new Class_('Bar'))
                ),
                true,
                false,
            ],
        ];
    }

    /**
     * @dataProvider data宣言要素が取得できること
     * @noinspection NonAsciiCharacters
     */
    public function test宣言要素が取得できること(DiagramUnit $sut, string $expected): void
    {
        // when
        $declaringElement = $sut->declaringElement();

        // then
        $this->assertSame($expected, $declaringElement);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, DiagramUnit|string>>
     */
    public function data宣言要素が取得できること(): array
    {
        $abstract = new Class_('Bar');
        $abstract->flags = Modifiers::ABSTRACT;
        return [
            '具象クラス' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper(new Class_('Bar'))
                ),
                'class',
            ],
            '抽象クラス' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper($abstract)
                ),
                'abstract',
            ],
            'インタフェース' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper(new Interface_('Bar'))
                ),
                'interface',
            ],
            'トレイト' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper(new Trait_('Bar'))
                ),
                'trait',
            ],
            'Enum' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper(new Enum_('Bar'))
                ),
                'enum',
            ],
            '以上のどれでもない場合デフォルトの宣言要素を返す' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    null
                ),
                'stereotype',
            ],
        ];
    }

    /**
     * @dataProvider dataトレイトを判定できること
     * @noinspection NonAsciiCharacters
     */
    public function testトレイトを判定できること(DiagramUnit $sut, bool $expected): void
    {
        // when
        $isTrait = $sut->isTrait();

        // then
        $this->assertSame($expected, $isTrait);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, DiagramUnit|bool>>
     */
    public function dataトレイトを判定できること(): array
    {
        return [
            '具象クラス' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper(new Class_('Bar'))
                ),
                false,
            ],
            'トレイト' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    new ClassLikeWrapper(new Trait_('Bar'))
                ),
                true,
            ],
            'クラス類ではない' => [
                new DiagramUnit(
                    '\Foo',
                    ['\Foo'],
                    false,
                    null
                ),
                false,
            ],
        ];
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testクラス類ではない時メソッドが1つも得られないこと(): void
    {
        // given
        $sut = new DiagramUnit(
            '\Foo',
            ['\Foo'],
            true,
            null
        );

        // when
        $methods = $sut->methods();

        // then
        $this->assertSame([], $methods);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testクラス類である時メソッドが得られること(): void
    {
        // given
        $expected = [new ClassMethod('bar')];
        $classLike =  new Class_(new Identifier('Foo'), ['stmts' => $expected]);
        $classLikeWrapper = new ClassLikeWrapper($classLike);
        $sut = new DiagramUnit(
            '\Foo',
            ['\Foo'],
            true,
            $classLikeWrapper
        );

        // when
        $methods = $sut->methods();

        // then
        $this->assertEquals($expected, $methods);
    }
}
