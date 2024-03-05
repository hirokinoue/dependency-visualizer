<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeWrapper;
use Hirokinoue\DependencyVisualizer\Config\Config;
use PhpParser\Node\Stmt\ClassMethod;

final class DiagramUnit
{
    private string $fullyQualifiedClassName;
    /**
     * @var DiagramUnit[] $classesDirectlyDependsOn
     */
    private array $classesDirectlyDependsOn = [];
    /** @var array<int, string> */
    private static array $visitedClasses = [];
    private bool $isRoot;
    // NOTE: nullになるのはDiagramUnitが定義済みクラスの場合とルートのDiagramUnitがクラスじゃない場合
    private ?ClassLikeWrapper $classLikeWrapper;
    private int $layer;

    public function __construct(
        string $fullyQualifiedClassName,
        bool $isRoot = false,
        ?ClassLikeWrapper $classLikeWrapper = null,
        int $layer = 0
    ) {
        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
        $this->isRoot = $isRoot;
        $this->classLikeWrapper = $classLikeWrapper;
        $this->layer = $layer;
    }

    public function push(DiagramUnit $other): void
    {
        if (!$this->hasBeenPushed($other)) {
            $this->classesDirectlyDependsOn[] = $other;
        }
    }

    public function fullyQualifiedClassName(): string {
        return $this->fullyQualifiedClassName;
    }

    public function className(): string {
        $parts = explode('\\', $this->fullyQualifiedClassName);
        return end($parts);
    }

    public function namespace(): string
    {
        $parts = explode('\\', $this->fullyQualifiedClassName);
        array_pop($parts);
        if (!empty($parts) && $parts[0] === '') {
            array_shift($parts);
        }
        return implode('\\', $parts);
    }

    public function isGlobal(): bool {
        return $this->namespace() === '';
    }

    /**
     * @return DiagramUnit[]
     */
    public function subClasses(): array {
        return $this->classesDirectlyDependsOn;
    }

    public function shouldStopTraverse(): bool
    {
        return $this->isEndOfAnalysis() || $this->hasReachedMaxDepth() || $this->hasBeenVisited();
    }

    private function isEndOfAnalysis(): bool
    {
        foreach (Config::endOfAnalysis() as $endOfAnalysis) {
            $pos = strpos($this->fullyQualifiedClassName, $endOfAnalysis);
            if ($pos === 0 || $pos === 1) {
                return true;
            }
        }
        return false;
    }

    private function hasReachedMaxDepth(): bool
    {
        return $this->layer === Config::maxDepth();
    }

    private function hasBeenPushed(DiagramUnit $other): bool
    {
        foreach ($this->classesDirectlyDependsOn as $diagramUnit) {
            if ($diagramUnit->fullyQualifiedClassName === $other->fullyQualifiedClassName) {
                return true;
            }
        }
        return false;
    }

    private function hasBeenVisited(): bool
    {
        if (in_array($this->fullyQualifiedClassName, self::$visitedClasses)) {
            return true;
        }
        return false;
    }

    public function registerVisitedClass(): void
    {
        self::$visitedClasses[] = $this->fullyQualifiedClassName;
    }

    public static function resetVisitedClasses(): void
    {
        self::$visitedClasses = [];
    }

    public static function countVisitedClasses(): int
    {
        return count(self::$visitedClasses);
    }


    public function isClassLikeRoot(): bool
    {
        return $this->isRoot && $this->classLikeWrapper !== null;
    }

    public function isNonClassLikeRoot(): bool
    {
        return $this->isRoot && $this->classLikeWrapper === null;
    }

    public function declaringElement(): string
    {
        if ($this->classLikeWrapper === null) {
            return ClassLikeWrapper::defaultDeclaringElement();
        }
        return $this->classLikeWrapper->declaringElement();
    }

    public function isTrait(): bool {
        return $this->classLikeWrapper !== null && $this->classLikeWrapper->isTrait();
    }

    /**
     * @return ClassMethod[]
     */
    public function methods(): array {
        if ($this->classLikeWrapper === null) {
            return [];
        }
        return $this->classLikeWrapper->methods();
    }

    public function nextLayer(): int
    {
        if ($this->layer === PHP_INT_MAX) {
            throw new \OverflowException("Layer limit exceeded.");
        }
        return $this->layer + 1;
    }
}
