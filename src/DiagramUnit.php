<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

final class DiagramUnit
{
    private string $fullyQualifiedClassName;
    /**
     * @var DiagramUnit[] $classesDirectlyDependsOn
     */
    private array $classesDirectlyDependsOn = [];

    public function __construct(string $fullyQualifiedClassName) {
        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
    }

    public function push(DiagramUnit $other): void
    {
        $this->classesDirectlyDependsOn[] = $other;
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
}

