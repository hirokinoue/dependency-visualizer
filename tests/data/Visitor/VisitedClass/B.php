<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass;

class B
{
    public function b()
    {
        new A();
    }
}
