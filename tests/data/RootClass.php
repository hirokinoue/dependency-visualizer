<?php

namespace Hirokinoue\DependencyVisualizer\Tests\data;

class RootClass {
    public function foo(\Error $e): void
    {
        new Bar();
        new Baz();
        new EmptyClass();
        Foo;
        new \stdClass;
    }
}
