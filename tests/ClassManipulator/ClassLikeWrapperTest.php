<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests\ClassManipulator;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeWrapper;
use PhpParser\Modifiers;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;

final class ClassLikeWrapperTest extends TestCase
{
    /**
     * @dataProvider dataクラスの名前が取得できること
     * @noinspection NonAsciiCharacters
     */
    public function testクラスの名前が取得できること(ClassLike $classLike, string $expected): void
    {
        // given
        $sut = new ClassLikeWrapper($classLike);

        // when
        $name = $sut->name();

        // then
        $this->assertSame($expected, $name);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, Class_|string>>
     */
    public function dataクラスの名前が取得できること(): array
    {
        $class1 = new Class_(new Identifier('Bar'));
        $class1->namespacedName = new FullyQualified('Foo\Bar');
        $class2 = new Class_(null);
        $class2->namespacedName = null;
        $class3 =  new Class_(new Identifier('Bar'));
        $class3->namespacedName = null;
        return [
            '完全修飾名が取得できること' => [
                $class1,
                '\Foo\Bar',
            ],
            '名前が空であること' => [
                $class2,
                '',
            ],
            '完全修飾名ではない名前が取得できること' => [
               $class3,
                'Bar',
            ],
        ];
    }

    /**
     * @dataProvider data宣言する要素が取得できること
     * @noinspection NonAsciiCharacters
     */
    public function test宣言する要素が取得できること(ClassLike $classLike, string $expected): void
    {
        // given
        $sut = new ClassLikeWrapper($classLike);

        // when
        $declaringElement = $sut->declaringElement();

        // then
        $this->assertSame($expected, $declaringElement);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, ClassLike|string>>
     */
    public function data宣言する要素が取得できること(): array
    {
        return [
            '具象クラス' => [
                new Class_(''),
                'class',
            ],
            '抽象クラス' => [
                new Class_('', ['flags' => Modifiers::ABSTRACT]),
                'abstract',
            ],
            'インタフェース' => [
                new Interface_(''),
                'interface',
            ],
            'トレイト' => [
                new Trait_(''),
                'trait',
            ],
            'enum' => [
                new Enum_(''),
                'enum',
            ],
        ];
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testメソッドが取得できること(): void
    {
        // given
        $expected = [new ClassMethod('bar')];
        $classLike =  new Class_(new Identifier('Foo'), ['stmts' => $expected]);
        $sut = new ClassLikeWrapper($classLike);

        // when
        $methods = $sut->methods();

        // then
        $this->assertEquals($expected, $methods);
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testメソッドがない時空の配列が返ること(): void
    {
        // given
        $classLike =  new Class_(new Identifier('Foo'));
        $sut = new ClassLikeWrapper($classLike);

        // when
        $methods = $sut->methods();

        // then
        $this->assertSame([], $methods);
    }
}
