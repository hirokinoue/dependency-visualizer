<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\VisitedClass;

class B
{
    public function b()
    {
        new A();
    }
}
