<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data;

class Foo
{
    public function foo(): void
    {
        new Bar();
        new Baz();
    }
}
