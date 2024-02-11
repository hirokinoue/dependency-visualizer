<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop;

class A
{
    public function a()
    {
        new A();
        new B();
    }
}
