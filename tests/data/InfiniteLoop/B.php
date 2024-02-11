<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop;

class B
{
    public function b()
    {
        new A();
        new C();
    }
}
