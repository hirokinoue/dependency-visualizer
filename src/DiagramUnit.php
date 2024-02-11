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

    /**
     * @param string[] $ancestors
     */
    public function __construct(string $fullyQualifiedClassName, array $ancestors = []) {
        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
        $this->ancestors = $ancestors;
    }

    public function push(DiagramUnit $other): void
    {
        $this->classesDirectlyDependsOn[] = $other;
        $other->isCirculating = in_array($other->className(), $this->ancestors, true);
    }

    public function className(): string {
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
}

