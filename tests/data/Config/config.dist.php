<?php declare(strict_types=1);
return $config = [
    'memoryLimit' => 'Foo',
    'maxDepth' => 5,
    'endOfAnalysis' => [
        'Bar\\',
    ],
    'excludeFromAnalysis' => [
        'Baz\\',
    ],
    'excludeFilePath' => [
        'src/Qux.php',
    ],
];
