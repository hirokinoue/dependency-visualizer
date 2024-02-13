<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\VisitedClass;

class C
{
    public function c()
    {
        new B();
    }
}
