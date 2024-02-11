<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

final class DependencyVisualizer
{
    /**
     * @var Stmt[]
     */
    private array $stmts;

    /**
     * @param Stmt[] $stmts
     */
    private function __construct(array $stmts) {
        $this->stmts = $stmts;
    }

    public static function create(string $filePath): self {
        $fileContent = \file_get_contents($filePath);
        $code = ($fileContent === false) ? '' : $fileContent;
        $parser = (new ParserFactory())->createForHostVersion();
        $stmts = $parser->parse($code);
        if ($stmts === null) {
            throw new \InvalidArgumentException('No ast found.');
        }
        return new self($stmts);
    }

    public function analyze(): DiagramUnit
    {
        $diagramUnit = new DiagramUnit($this->rootClassName());

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor(new ClassVisitor($diagramUnit));
        $nodeTraverser->traverse($this->stmts);

        return $diagramUnit;
    }

    private function rootClassName(): string
    {
        $classLike = ClassLikeNodeFinder::create($this->stmts);
        if ($classLike === null || $classLike->classLikeName() === '') {
            // 分析の始点となるファイルがクラスではないケース
            return 'root';
        }
        // 分析の始点となるファイルがクラスのケース
        return $classLike->classLikeName();
    }
}
