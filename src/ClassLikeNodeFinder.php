<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitor\NameResolver;

class ClassLikeNodeFinder
{
    /**
     * @param Node[] $ast
     */
    public static function find(array $ast): ?ClassLikeWrapper {
        $nodeFinder = new NodeFinder();
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $ast = $nodeTraverser->traverse($ast);
        $shouldClassLikeNodeOrNull = $nodeFinder->findFirstInstanceOf($ast, ClassLike::class);

        if ($shouldClassLikeNodeOrNull === null) {
            return null;
        }
        return new ClassLikeWrapper($shouldClassLikeNodeOrNull);
    }
}
