<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

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

    /**
     * @param string[] $ancestors
     */
    public function __construct(string $fullyQualifiedClassName, array $ancestors = []) {
        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
        $this->ancestors = $ancestors;
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
}
