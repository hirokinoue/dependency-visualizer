<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Exporter;

use Hirokinoue\DependencyVisualizer\DiagramUnit;

final class StringExporter implements Exporter
{
    private string $buffer;

    public function __construct() {
        $this->buffer = '';
    }

    public function export(DiagramUnit $diagramUnit): string
    {
        $indent = '';
        $this->buffer .= $indent . $diagramUnit->fullyQualifiedClassName() . PHP_EOL;
        $this->writeBuffer($diagramUnit, $indent);
        return $this->buffer;
    }

    private function writeBuffer(DiagramUnit $diagramUnit, string $indent = ''): void
    {
        $indent .= '  ';
        foreach ($diagramUnit->subClasses() as $subClass) {
            $this->buffer .= $indent . $subClass->fullyQualifiedClassName() . PHP_EOL;
            $this->writeBuffer($subClass, $indent);
        }
    }
}
