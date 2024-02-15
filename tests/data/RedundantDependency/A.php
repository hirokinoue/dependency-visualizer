<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\RedundantDependency;

class A
{
    public function a()
    {
        new B();
        new C();
        new B();
    }
}
