<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Visitor;

use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLikeNodeFinder;
use Hirokinoue\DependencyVisualizer\ClassManipulator\ClassLoader;
use Hirokinoue\DependencyVisualizer\Config\Config;
use Hirokinoue\DependencyVisualizer\DiagramUnit;
use Hirokinoue\DependencyVisualizer\Logger;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;

final class ClassVisitor extends NodeVisitorAbstract
{
    private DiagramUnit $diagramUnit;

    public function __construct(DiagramUnit $diagramUnit) {
        $diagramUnit->registerVisitedClass();
        $this->diagramUnit = $diagramUnit;
        Logger::info('instantiate ClassVisitor', ['name' => $diagramUnit->fullyQualifiedClassName()]);
    }

    public function enterNode(Node $node) {
        if ($this->diagramUnit->shouldStopTraverse()) {
            return $node;
        }

        if (!$node instanceof FullyQualified) {
            return $node;
        }

        if ($this->isExcludedNamespace($node)) {
            return $node;
        }

        $classFile = ClassLoader::create($node);
        if ($classFile->isClass()) {
            Logger::info('load class', ['name' => $classFile->className()]);
            $ancestors = $this->diagramUnit->ancestors();
            $ancestors[] = $node->toCodeString();

            $stmts = $classFile->stmts();
            $classLike = ClassLikeNodeFinder::find($stmts);

            $subClass = new DiagramUnit(
                $classFile->className(),
                $ancestors,
                false,
                $classLike,
                $this->diagramUnit->nextLayer()
            );
            $this->diagramUnit->push($subClass);

            if ($stmts === [] || $classFile->notLoaded() || $subClass->hasBeenVisited()) {
                return $node;
            }

            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor(new NameResolver());
            $nodeTraverser->addVisitor(new self($subClass));
            $nodeTraverser->traverse($stmts);
        }
        return $node;
    }

    private function isExcludedNamespace(FullyQualified $node): bool {
        foreach (Config::excludeFromAnalysis() as $excludeFromAnalysis) {
            $pos = strpos($node->toCodeString(), $excludeFromAnalysis);
            if ($pos === 0 || $pos === 1) {
                return true;
            }
        }
        return false;
    }
}
