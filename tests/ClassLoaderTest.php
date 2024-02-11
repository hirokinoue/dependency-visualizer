<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Tests;

use Hirokinoue\DependencyVisualizer\ClassLoader;
use PhpParser\Node\Name\FullyQualified;
use PHPUnit\Framework\TestCase;

final class ClassLoaderTest extends TestCase
{
    /**
     * @dataProvider data対象に応じてロードできること
     * @noinspection NonAsciiCharacters
     */
    public function test対象に応じてロードできること(FullyQualified $fullyQualified, string $expectedClassName, string $expectedCode): void
    {
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
     * @return array<string, array<int, string|FullyQualified>>
     */
    public function data対象に応じてロードできること(): array
    {
        $userDefinedClass = <<<CODE
<?php
namespace Hirokinoue\DependencyVisualizer\Tests\data;
class UserDefinedClass{}

CODE;
        return [
            'ユーザー定義クラスはクラス名とコードが取得できる' => [
                new FullyQualified('Hirokinoue\DependencyVisualizer\Tests\data\UserDefinedClass'),
                '\Hirokinoue\DependencyVisualizer\Tests\data\UserDefinedClass',
                $userDefinedClass,
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
}
