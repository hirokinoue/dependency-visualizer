<?php declare(strict_types=1);
return $config = [
    'memoryLimit' => '1024M',
    'maxDepth' => 5,
    // Specify a part of a namespace to exclude classes from the analysis.
    // For example, when dependencies of third-party packages are not wanted to be analyzed.
    // Classes starting with the given namespace will not be analyzed and classes they depend on will not.
    // Note that if A depends on B and C, and B depends on C, and B is excluded,
    // the dependence from A to C will be shown, but the dependence from B to C will not be shown.
    'endOfAnalysis' => [
        'PhpParser\\',
    ],
    // Specify a part of a namespace.
    // Classes starting with the given namespace will not be analyzed.
    'excludeFromAnalysis' => [
        'PhpParser\Node\\',
    ],
    // Specify the relative path from the directory where the application is executed.
    'excludeFilePath' => [
        '',
    ],
];
