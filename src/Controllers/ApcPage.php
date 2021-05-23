<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood\Controllers;

use \LupusMichaelis\PHPHood\App;

class ApcPage
	implements \LupusMichaelis\PHPHood\Controller
{
	public function __invoke(App $app): void
	{
		if(file_exists('apc.php'))
		{
			header('Location: ./apc.php');
			die();
		}

		$configuration = $app->getConfiguration()->get();

		if(!isset($configuration['apc']['provided-monitor']))
		{
			header('HTTP/1.1 404 Not found');
			header('Content-type: text/plain');
			die('APCu hood not configured');
		}

		$apc_scriptname = $configuration['apc']['provided-monitor'];

		$copied = @copy($apc_scriptname, 'apc.php');

		if(!$copied)
		{
			$app->logError(sprintf
				( 'Couldn\'t copy \'%s\' because \'%s\''
				, $configuration['apc']['provided-monitor']
				, error_get_last()['message']
				));

			header('HTTP/1.1 404 Not found');
			header('Content-type: text/plain');
			die(sprintf('File \'%s\' not found', 'apc.php'));
		}

		header('Location: ./apc.php');
		die();
	}
}
