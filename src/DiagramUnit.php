<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeWrapper;
use PhpParser\Node\Stmt\ClassMethod;

final class DiagramUnit
{
    private string $fullyQualifiedClassName;
    /**
     * @var DiagramUnit[] $classesDirectlyDependsOn
     */
    private array $classesDirectlyDependsOn = [];
    /**
     * @var string[] $ancestors Array of fully qualified class name.
     */
    private array $ancestors;
    private bool $isCirculating = false;
    /** @var array<int, string> */
    private static array $visitedClasses = [];
    private bool $isRoot;
    // NOTE: nullになるのはDiagramUnitが定義済みクラスの場合とルートのDiagramUnitがクラスじゃない場合
    private ?ClassLikeWrapper $classLikeWrapper;

    /**
     * @param string[] $ancestors
     */
    public function __construct(string $fullyQualifiedClassName, array $ancestors = [], bool $isRoot = false, ?ClassLikeWrapper $classLikeWrapper = null) {
        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
        $this->ancestors = $ancestors;
        $this->isRoot = $isRoot;
        $this->classLikeWrapper = $classLikeWrapper;
    }

    public function push(DiagramUnit $other): void
    {
        if (!$this->hasBeenPushed($other)) {
            $this->classesDirectlyDependsOn[] = $other;
        }
        $other->isCirculating = in_array($other->fullyQualifiedClassName(), $this->ancestors, true);
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

    /**
     * @return string[]
     */
    public function ancestors(): array {
        return $this->ancestors;
    }

    public function shouldStopTraverse(): bool
    {
        return $this->isCirculating;
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

    public function hasBeenVisited(): bool
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
}
