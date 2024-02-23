<?php declare(strict_types=1);

namespace Hirokinoue\DependencyVisualizer\Exporter;

use Hirokinoue\DependencyVisualizer\DiagramUnit;

interface Exporter
{
    public function export(DiagramUnit $diagramUnit): string;
}
