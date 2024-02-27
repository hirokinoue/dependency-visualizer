<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests\ClassManipulator;

use Hirokinoue\DependencyVisualizer\Config\Config;
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
     */
    public function data対象に応じてロードできること(): \Generator
    {
        $userDefinedClassCode = <<<CODE
<?php
namespace Hirokinoue\DependencyVisualizer\Tests\data\ClassManipulator;
class UserDefinedClass{}

CODE;
        yield 'ユーザー定義クラスはクラス名とコードが取得できる' => [
            new FullyQualified('Hirokinoue\DependencyVisualizer\Tests\data\ClassManipulator\UserDefinedClass'),
            '\Hirokinoue\DependencyVisualizer\Tests\data\ClassManipulator\UserDefinedClass',
            $userDefinedClassCode,
        ];
        yield '内部クラスはクラス名のみ取得できる' => [
            new FullyQualified('Exception'),
            '\Exception',
            '',
        ];
        yield '定数はクラス名もコードも取得できない' => [
            new FullyQualified('FOO'),
            '',
            '',
        ];
        yield 'キーワードはクラス名もコードも取得できない' => [
            new FullyQualified('true'),
            '',
            '',
        ];
    }

    /**
     * @noinspection NonAsciiCharacters
     */
    public function test除外指定されたファイルのユーザー定義クラスはクラス名のみ取得できる(): void {
        // given
        Config::initialize(__DIR__ . '/../data/ClassManipulator/Exclude');
        $sut = ClassLoader::create(new FullyQualified(
            'Hirokinoue\DependencyVisualizer\Tests\data\ClassManipulator\UserDefinedClass'
        ));

        // when
        $className = $sut->className();
        $code = $sut->content();

        // then
        $this->assertSame('\Hirokinoue\DependencyVisualizer\Tests\data\ClassManipulator\UserDefinedClass', $className);
        $this->assertSame('', $code);
    }

    /**
     * @dataProvider dataロードしたことのあるクラスを再びロードしないこと
     * @noinspection NonAsciiCharacters
     */
    public function testロードしたことのあるクラスを再びロードしないこと(
        FullyQualified $fullyQualified,
        string $expectedClassName,
        string $expectedCode,
        callable $setUp
    ): void {
        // given
        $setUp();
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
     */
    public function dataロードしたことのあるクラスを再びロードしないこと(): \Generator
    {
        $setUp = function () {
            Config::initialize(__DIR__ . '/../data/ClassManipulator/NonExclude');
            ClassLoader::resetLoadedClasses();
        };
        $userDefinedClass = 'Hirokinoue\DependencyVisualizer\Tests\data\ClassManipulator\UserDefinedClass';
        $userDefinedClassCode = <<<CODE
<?php
namespace Hirokinoue\DependencyVisualizer\Tests\data\ClassManipulator;
class UserDefinedClass{}

CODE;
        yield '初回はロードする' => [
            new FullyQualified($userDefinedClass),
            '\\' . $userDefinedClass,
            $userDefinedClassCode,
            $setUp,
        ];
        yield '2回目以降はロードしない' => [
            new FullyQualified($userDefinedClass),
            '\\' . $userDefinedClass,
            '',
            function () {},
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
