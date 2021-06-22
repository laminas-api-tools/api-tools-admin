<?php

declare(strict_types=1);

// Mock zfdeploy.php
$package = $argv[2];
file_put_contents($package, 'test');
