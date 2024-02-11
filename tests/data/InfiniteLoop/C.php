<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data\InfiniteLoop;

class C
{
    public function c()
    {
        new A();
    }
}
