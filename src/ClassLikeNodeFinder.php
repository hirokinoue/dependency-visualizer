<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitor\NameResolver;

class ClassLikeNodeFinder
{
    private ClassLike $node;

    private function __construct(ClassLike $node) {
        $this->node = $node;
    }

    /**
     * @param Node[] $ast
     */
    public static function create(array $ast): ?self {
        $nodeFinder = new NodeFinder();
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $ast = $nodeTraverser->traverse($ast);
        $shouldClassLikeNodeOrNull = $nodeFinder->findFirstInstanceOf($ast, ClassLike::class);

        if ($shouldClassLikeNodeOrNull === null) {
            return null;
        }
        return new self($shouldClassLikeNodeOrNull);
    }

    public function classLikeName(): string {
        if ($this->node->namespacedName !== null) {
            return (new FullyQualified($this->node->namespacedName->name))->toCodeString();
        }
        if ($this->node->name === null) {
            return '';
        }
        return $this->node->name->name;
    }
}
