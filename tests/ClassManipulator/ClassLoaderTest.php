<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests\ClassManipulator;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLoader;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PHPUnit\Framework\TestCase;

final class ClassLoaderTest extends TestCase
{
    /**
     * @dataProvider data対象に応じてロードできること
     * @noinspection NonAsciiCharacters
     */
    public function test対象に応じてロードできること(
        FullyQualified $fullyQualified,
        string $expectedClassName,
        string $expectedCode
    ): void {
        // given
        ClassLoader::resetLoadedClasses();
        $sut = ClassLoader::create($fullyQualified);

        // when
        $className = $sut->className();
        $code = $sut->content();

        // then
        $this->assertSame($expectedClassName, $className);
        $this->assertSame($expectedCode, $code);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, string|FullyQualified>>
     */
    public function data対象に応じてロードできること(): array
    {
        $userDefinedClassCode = <<<CODE
<?php
namespace Hirokinoue\DependencyVisualizer\Tests\data;
class UserDefinedClass{}

CODE;
        return [
            'ユーザー定義クラスはクラス名とコードが取得できる' => [
                new FullyQualified('Hirokinoue\DependencyVisualizer\Tests\data\UserDefinedClass'),
                '\Hirokinoue\DependencyVisualizer\Tests\data\UserDefinedClass',
                $userDefinedClassCode,
            ],
            '内部クラスはクラス名のみ取得できる' => [
                new FullyQualified('Exception'),
                '\Exception',
                '',
            ],
            '定数はクラス名もコードも取得できない' => [
                new FullyQualified('FOO'),
                '',
                '',
            ],
            'キーワードはクラス名もコードも取得できない' => [
                new FullyQualified('true'),
                '',
                '',
            ],
        ];
    }

    /**
     * @dataProvider dataロードしたことのあるクラスを再びロードしないこと
     * @noinspection NonAsciiCharacters
     */
    public function testロードしたことのあるクラスを再びロードしないこと(
        FullyQualified $fullyQualified,
        string $expectedClassName,
        string $expectedCode
    ): void {
        // given
        $sut = ClassLoader::create($fullyQualified);

        // when
        $className = $sut->className();
        $code = $sut->content();

        // then
        $this->assertSame($expectedClassName, $className);
        $this->assertSame($expectedCode, $code);
    }

    /**
     * @noinspection NonAsciiCharacters
     * @return array<string, array<int, FullyQualified|string|bool>>
     */
    public function dataロードしたことのあるクラスを再びロードしないこと(): array
    {
        $userDefinedClass = 'Hirokinoue\DependencyVisualizer\Tests\data\UserDefinedClass';
        $userDefinedClassCode = <<<CODE
<?php
namespace Hirokinoue\DependencyVisualizer\Tests\data;
class UserDefinedClass{}

CODE;
        return [
            '初回はロードする' => [
                new FullyQualified($userDefinedClass),
                '\\' . $userDefinedClass,
                $userDefinedClassCode,
            ],
            '2回目以降はロードしない' => [
                new FullyQualified($userDefinedClass),
                '\\' . $userDefinedClass,
                '',
            ],
        ];
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testStmtノードが取得できること(): void
    {
        // given
        $sut = ClassLoader::create(new FullyQualified('Hirokinoue\DependencyVisualizer\Tests\data\Foo'));

        // when
        $stmts = $sut->stmts();

        // then
        foreach ($stmts as $stmt) {
            $this->assertInstanceOf(Stmt::class, $stmt);
        }
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function testStmtノードが取得できないこと(): void
    {
        // given
        $sut = ClassLoader::create(new FullyQualified('Exception'));

        // when
        $stmts = $sut->stmts();

        // then
        $this->assertEquals([], $stmts);
    }
}
