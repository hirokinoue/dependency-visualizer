<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Exporter;

use Hirokinoue\DependencyVisualizer\DiagramUnit;
use InvalidArgumentException;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;

/**
 * @see https://plantuml.com/ja/class-diagram
 */
final class PlantUmlExporter implements Exporter
{
    /** @var string[] fully qualified name */
    private array $drawnClasses = [];
    /** @var string[]  */
    private array $drawnDependencies = [];
    private string $diagram = '';
    private bool $drawMethod;

    public function __construct(bool $drawMethod = false)
    {
        $this->drawMethod = $drawMethod;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function export(DiagramUnit $diagramUnit): string
    {
        $this->diagram = '@startuml' . PHP_EOL;
        $this->drawClassLike($diagramUnit);
        $this->drawDependency($diagramUnit);
        $this->diagram .= '@enduml';
        return $this->diagram;
    }

    private function drawClassLike(DiagramUnit $diagramUnit): void
    {
        if (in_array($diagramUnit->fullyQualifiedClassName(), $this->drawnClasses, true)) {
            return;
        }
        $this->drawnClasses[] = $diagramUnit->fullyQualifiedClassName();

        $methods = $this->drawMethod ? $this->drawMethod($diagramUnit) : "";
        $classDiagram = $this->diagramShape($diagramUnit) .
            $this->replaceErrorStr($diagramUnit) .
            $this->rootAnnotation($diagramUnit) .
            $this->traitAnnotation($diagramUnit) .
            $methods . PHP_EOL;

        $this->diagram .= $diagramUnit->isGlobal()
            ? $classDiagram
            : "package {$diagramUnit->namespace()} <<Folder>> {" . PHP_EOL . $classDiagram . "}" . PHP_EOL;

        foreach ($diagramUnit->subClasses() as $subClass) {
            $this->drawClassLike($subClass);
        }
    }

    private function drawDependency(DiagramUnit $diagramUnit): void
    {
        foreach ($diagramUnit->subClasses() as $dependent) {
            $lineForCheck = "{$diagramUnit->fullyQualifiedClassName()} --> {$dependent->fullyQualifiedClassName()}";
            if (in_array($lineForCheck, $this->drawnDependencies, true)) {
                continue;
            }
            $this->drawnDependencies[] = $lineForCheck;

            $this->diagram .= "{$this->diagramIdentifier($diagramUnit)} --> {$this->diagramIdentifier($dependent)}" . PHP_EOL;
            $this->drawDependency($dependent);
        }
    }

    private function diagramIdentifier(DiagramUnit $diagramUnit): string
    {
        $className = $this->replaceErrorStr($diagramUnit);
        $namespace = ($diagramUnit->namespace() === '') ? '' : "{$diagramUnit->namespace()}.";
        return $namespace . $className;
    }

    private function diagramShape(DiagramUnit $diagramUnit): string
    {
        if ($diagramUnit->isNonClassLikeRoot()) {
            return 'circle ';
        }
        if ($diagramUnit->isTrait()) {
            return 'abstract ';
        }
        return $diagramUnit->declaringElement() . ' ';
    }

    private function replaceErrorStr(DiagramUnit $diagramUnit): string
    {
        return \str_replace(array('.php', '.', '-'), array('', '_', '_'), $diagramUnit->className());
    }

    private function traitAnnotation(DiagramUnit $diagramUnit): string
    {
        return ($diagramUnit->isTrait() ? ' <<trait>>' : '');
    }

    private function rootAnnotation(DiagramUnit $diagramUnit): string
    {
        return $diagramUnit->isClassLikeRoot() ? ' <<root>>' : '';
    }

    private function drawMethod(DiagramUnit $diagramUnit): string
    {
        if ($diagramUnit->isNonClassLikeRoot() || count($diagramUnit->methods()) === 0) {
            return '';
        }

        $result = '{' . PHP_EOL;
        foreach ($diagramUnit->methods() as $method) {
            $result .= "{$this->visibility($method)}{$method->name->name}(){$this->returnType($method)}" . PHP_EOL;
        }
        $result .= '}';
        return $result;
    }

    private function visibility(ClassMethod $method): string
    {
        if ($method->isPublic()) {
            return '+';
        }
        if ($method->isPrivate()) {
            return '-';
        }
        return '#';
    }

    private function returnType(ClassMethod $method): string
    {
        if ($method->returnType === null) {
            return '';
        }
        if ($method->returnType instanceof Identifier) {
            return ': ' . $method->returnType->name;
        }
        if ($method->returnType instanceof Name) {
            return ': ' . $method->returnType->getLast();
        }
        if ($method->returnType instanceof NullableType) {
            if ($method->returnType->type instanceof Identifier) {
                return ': ' . $method->returnType->type->name;
            }
            if ($method->returnType->type instanceof Name) {
                return ': ' . $method->returnType->type->getLast();
            }
        }
        if ($method->returnType instanceof UnionType) {
            $names = [];
            foreach ($method->returnType->types as $type) {
                if ($type instanceof Identifier) {
                    $names[] = $type->name;
                }
                if ($type instanceof Name) {
                    $names[] = $type->getLast();
                }
                if ($type instanceof IntersectionType) {
                    $names[] = $this->nameFromIntersectionType($type);
                }
            }
            return ': ' . \implode('|', $names);
        }
        if ($method->returnType instanceof IntersectionType) {
            return ': ' . $this->nameFromIntersectionType($method->returnType);
        }
        return '';
    }

    private function nameFromIntersectionType(IntersectionType $type): string
    {
        $names = [];
        foreach ($type->types as $eachType) {
            if ($eachType instanceof Identifier) {
                $names[] = $eachType->name;
            }
            if ($eachType instanceof Name) {
                $names[] = $eachType->getLast();
            }
        }
        return \implode('&', $names);
    }
}
