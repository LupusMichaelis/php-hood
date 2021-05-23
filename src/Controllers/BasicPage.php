<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood\Controllers;

use \LupusMichaelis\PHPHood\App;

class BasicPage
{
	static
	public function PHPInfo(App $app): void
	{
		phpinfo();
		die();
	}

	static
	public function APCUStats()
	{
		header('Content-type: application/json');

		if(function_exists('\apcu_cache_info'))
			die(json_encode(apcu_cache_info()));

		die(json_encode(['error' => 'Function \'\\apcu_cache_info\' doesn\'t exist']));
	}
}
