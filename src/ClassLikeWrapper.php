<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

class ClassLikeWrapper
{
    private const ABSTRACT = 'abstract';
    private const CLASS_ = 'class';
    private const ENUM  = 'enum';
    private const INTERFACE = 'interface';
    private const STEREOTYPE = 'stereotype';
    private const TRAIT = 'trait';

    private ClassLike $node;

    public function __construct(ClassLike $node) {
        $this->node = $node;
    }

    public function name(): string {
        if ($this->node->namespacedName !== null) {
            return (new FullyQualified($this->node->namespacedName->name))->toCodeString();
        }
        if ($this->node->name === null) {
            return '';
        }
        return $this->node->name->name;
    }

    public function declaringElement(): string
    {
        $node = $this->node;
        if ($node instanceof Class_ && $node->isAbstract()) {
            return self::ABSTRACT;
        }
        if ($node instanceof Class_ && !$node->isAbstract()) {
            return self::CLASS_;
        }
        if ($node instanceof Interface_) {
            return self::INTERFACE;
        }
        if ($node instanceof Trait_) {
            return self::TRAIT;
        }
        if ($node instanceof Enum_) {
            return self::ENUM;
        }
        // NOTE: PHP-Parser 5.0.0ではありえないケース
        return self::defaultDeclaringElement();
    }

    public function isTrait(): bool
    {
        return $this->node instanceof Trait_;
    }

    public static function defaultDeclaringElement(): string
    {
        return self::STEREOTYPE;
    }

    /**
     * @return ClassMethod[]
     */
    public function methods(): array
    {
        return $this->node->getMethods();
    }
}
