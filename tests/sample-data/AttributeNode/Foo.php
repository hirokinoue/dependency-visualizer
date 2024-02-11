<?php

#[Attribute]
class Foo{}

class Bar{
    #[Foo]
    public string $baz;
}
