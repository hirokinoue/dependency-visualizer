<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeWrapper;
use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLoader;
use Hirokinoue\DependencyVisualizer\Config\Config;
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
        $this->assertSame('Foo', $namespace);
    }

    /**
     * @dataProvider data指定された名前空間のクラスをトラバースしないこと
     * @noinspection NonAsciiCharacters
     */
    public function test指定された名前空間のクラスをトラバースしないこと(string $path): void
    {
        // given
        Config::initialize($path);
        $sut = new DiagramUnit(
            '\Bar\Foo',
            false,
            null
        );

        // when
        $shouldStop = $sut->shouldStopTraverse();

        // then
        $this->assertTrue($shouldStop);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function data指定された名前空間のクラスをトラバースしないこと(): \Generator
    {
        yield '名前空間を完全修飾名で指定する' => [
            __DIR__ . '/data/DiagramUnit/Config/0',
        ];
        yield '名前空間を修飾名で指定する' => [
            __DIR__ . '/data/DiagramUnit/Config/1',
        ];
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function test解析したことのあるクラスを再び解析しない(): void
    {
        // given
        $sut = new DiagramUnit(
            '\Foo\Bar',
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
     */
    public function data解析結果のルートを見分けられること(): \Generator
    {
        yield '解析結果がルートではない_クラスのファイルがない' => [
            new DiagramUnit(
                '\Foo',
                false,
                null
            ),
            false,
            false,
        ];
        yield '解析結果がルートではない_クラスのファイルがある' => [
            new DiagramUnit(
                '\Foo',
                false,
                new ClassLikeWrapper(new Class_('Bar'))
            ),
            false,
            false,
        ];
        yield '解析結果がルート_クラスのファイルがない' => [
            new DiagramUnit(
                '\Foo',
                true,
                null
            ),
            false,
            true,
        ];
        yield '解析結果がルート_クラスのファイルがある' => [
            new DiagramUnit(
                '\Foo',
                true,
                new ClassLikeWrapper(new Class_('Bar'))
            ),
            true,
            false,
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
                    false,
                    new ClassLikeWrapper(new Class_('Bar'))
                ),
                'class',
            ],
            '抽象クラス' => [
                new DiagramUnit(
                    '\Foo',
                    false,
                    new ClassLikeWrapper($abstract)
                ),
                'abstract',
            ],
            'インタフェース' => [
                new DiagramUnit(
                    '\Foo',
                    false,
                    new ClassLikeWrapper(new Interface_('Bar'))
                ),
                'interface',
            ],
            'トレイト' => [
                new DiagramUnit(
                    '\Foo',
                    false,
                    new ClassLikeWrapper(new Trait_('Bar'))
                ),
                'trait',
            ],
            'Enum' => [
                new DiagramUnit(
                    '\Foo',
                    false,
                    new ClassLikeWrapper(new Enum_('Bar'))
                ),
                'enum',
            ],
            '以上のどれでもない場合デフォルトの宣言要素を返す' => [
                new DiagramUnit(
                    '\Foo',
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
     */
    public function dataトレイトを判定できること(): \Generator
    {
        yield '具象クラス' => [
            new DiagramUnit(
                '\Foo',
                false,
                new ClassLikeWrapper(new Class_('Bar'))
            ),
            false,
        ];
        yield 'トレイト' => [
            new DiagramUnit(
                '\Foo',
                false,
                new ClassLikeWrapper(new Trait_('Bar'))
            ),
            true,
        ];
        yield 'クラス類ではない' => [
            new DiagramUnit(
                '\Foo',
                false,
                null
            ),
            false,
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
            true,
            $classLikeWrapper
        );

        // when
        $methods = $sut->methods();

        // then
        $this->assertEquals($expected, $methods);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function test次の階層が得られること(): void
    {
        // given
        $sut = new DiagramUnit(
            '\Foo',
            true,
            null,
            0
        );

        // when
        $nextLayer = $sut->nextLayer();

        // then
        $this->assertSame(1, $nextLayer);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function test階層が上限を超えると例外を送出すること(): void
    {
        // given
        $sut = new DiagramUnit(
            '\Foo',
            true,
            null,
            PHP_INT_MAX
        );
        $this->expectException(\OverflowException::class);

        // when
        $sut->nextLayer();
    }
}
