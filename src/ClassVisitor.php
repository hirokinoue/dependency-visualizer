<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

final class ClassVisitor extends NodeVisitorAbstract
{
    private DiagramUnit $diagramUnit;

    public function __construct(DiagramUnit $diagramUnit) {
        $this->diagramUnit = $diagramUnit;
    }

    public function enterNode(Node $node) {
        if (!$node instanceof FullyQualified) {
            return $node;
        }

        $classFile = ClassLoader::create($node);
        if ($classFile->isClass()) {
            $ancestors = $this->diagramUnit->ancestors();
            $ancestors[] = $node->toCodeString();

            $subClass = new DiagramUnit($classFile->className(), $ancestors);
            $this->diagramUnit->push($subClass);

            if ($classFile->codeNotFound()) {
                return $node;
            }

            $parser = (new ParserFactory())->createForHostVersion();
            $stmts = $parser->parse($classFile->content());
            if ($stmts === null) {
                return $node;
            }

            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor(new NameResolver());
            $nodeTraverser->addVisitor(new self($subClass));
            $nodeTraverser->traverse($stmts);
        }
        return $node;
    }
}
