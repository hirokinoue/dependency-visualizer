<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data;

class Baz
{
    public function baz(): void
    {
        new Qux();
    }
}
