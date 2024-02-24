<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass;

class C
{
    public function c()
    {
        new B();
    }
}
