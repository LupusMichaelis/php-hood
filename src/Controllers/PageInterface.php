<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood\Controllers;

use \LupusMichaelis\PHPHood\App;

interface PageInterface
{
	function __invoke(App $app):void ;
}
