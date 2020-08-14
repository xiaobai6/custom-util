<?php
require_once '../vendor/autoload.php';

use xiaobai6\Verify;

$mobile = '13286769586';

var_dump(Verify::isMobile($mobile));