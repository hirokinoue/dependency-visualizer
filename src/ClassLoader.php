<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use ReflectionClass;

final class ClassLoader
{
    private string $fullyQualifiedName;
    private string $content;
    /** @var array<int, string> */
    private static array $loadedClasses = [];
    /** @var null|Stmt[] $stmts */
    private ?array $stmts = null;

    private function __construct(string $fullyQualifiedName, string $content) {
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->content = $content;
    }

    public static function create(FullyQualified $fullyQualified): self
    {
        $fullyQualifiedName = $fullyQualified->toCodeString();

        if (self::hasBeenLoaded($fullyQualifiedName)) {
            return new self($fullyQualifiedName, '');
        }

        try {
            // ReflectionClassにかけてみないと存在するクラスなのかどうかがわからないため
            // $qualifiedNameがclass-stringであることを保証できない
            /** @phpstan-ignore-next-line */
            $reflector = new ReflectionClass($fullyQualifiedName);
        }
        catch (\ReflectionException $r) {
            // $qualifiedNameがクラス名ではないケースやクラスのファイルはあるが中身が無いケース
            return new self('', '');
        }

        $path = ($reflector->getFileName() === false) ? '' : $reflector->getFileName();
        $code = self::readFile($path);

        self::$loadedClasses[] = $fullyQualifiedName;

        // 定義済みクラスは$codeが空
        return new self($fullyQualifiedName, $code);
    }

    private static function readFile(string $path): string {
        if ($path === '') {
            return '';
        }

        $content = \file_get_contents($path);
        if ($content === false) {
            return '';
        }

        return $content;
    }

    public function className(): string {
        return $this->fullyQualifiedName;
    }

    public function content(): string {
        return $this->content;
    }

    /**
     * @return Stmt[]
     */
    public function stmts(): array {
        if ($this->stmts !== null) {
            return $this->stmts;
        }

        $parser = (new ParserFactory())->createForHostVersion();
        $stmts = $parser->parse($this->content());
        if ($stmts === null) {
            return $this->stmts = [];
        }
        return $this->stmts = $stmts;
    }

    public function isClass(): bool {
        return $this->fullyQualifiedName !== '';
    }

    public function notLoaded(): bool {
        return $this->content === '';
    }

    private static function hasBeenLoaded(string $fullyQualifiedName): bool
    {
        if (in_array($fullyQualifiedName, self::$loadedClasses)) {
            return true;
        }
        return false;
    }

    public static function resetLoadedClasses(): void
    {
        self::$loadedClasses = [];
    }
}
