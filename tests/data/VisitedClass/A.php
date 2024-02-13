<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\VisitedClass;

class A
{
    public function a()
    {
        new B();
        new C();
    }
}
