<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\Visitor\VisitedClass;

class A
{
    public function a()
    {
        new B();
        new C();
    }
}
