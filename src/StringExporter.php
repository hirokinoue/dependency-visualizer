<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer;

final class StringExporter
{
    private string $buffer;

    public function __construct() {
        $this->buffer = '';
    }

    public function export(DiagramUnit $diagramUnit, string $indent = ''): string
    {
        $this->buffer .= $indent . $diagramUnit->className() . PHP_EOL;
        $this->writeBuffer($diagramUnit, $indent);
        return $this->buffer;
    }

    private function writeBuffer(DiagramUnit $diagramUnit, string $indent = ''): void
    {
        $indent .= '  ';
        foreach ($diagramUnit->subClasses() as $subClass) {
            $this->buffer .= $indent . $subClass->className() . PHP_EOL;
            $this->writeBuffer($subClass, $indent);
        }
    }
}
