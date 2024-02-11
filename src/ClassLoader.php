<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use PhpParser\Node\Name\FullyQualified;
use ReflectionClass;

final class ClassLoader
{
    private string $fullyQualified;
    private string $content;

    private function __construct(string $fullyQualified, string $content) {
        $this->fullyQualified = $fullyQualified;
        $this->content = $content;
    }

    public static function create(FullyQualified $fullyQualified): self
    {
        try {
            // ReflectionClassにかけてみないと存在するクラスなのかどうかがわからないため
            // $qualifiedNameがclass-stringであることを保証できない
            /** @phpstan-ignore-next-line */
            $reflector = new ReflectionClass($fullyQualified->toCodeString());
        }
        catch (\ReflectionException $r) {
            // $qualifiedNameがクラス名ではないケースやクラスのファイルはあるが中身が無いケース
            return new self('', '');
        }

        $path = ($reflector->getFileName() === false) ? '' : $reflector->getFileName();
        $code = self::readFile($path);

        // 定義済みクラスは$codeが空
        return new self($fullyQualified->toCodeString(), $code);
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
        return $this->fullyQualified;
    }

    public function content(): string {
        return $this->content;
    }

    public function isClass(): bool {
        return $this->fullyQualified !== '';
    }

    public function codeNotFound(): bool {
        return $this->content === '';
    }
}
