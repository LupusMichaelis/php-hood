<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

spl_autoload_register(new class
	{
		const install_path = '/home/anvil/hood-lib';
		const script_suffix = '.php';

		function __invoke(string $class_fqn)
		{
			if(0 !== stripos($class_fqn, __NAMESPACE__))
				return;

			$vendor_class_name = substr($class_fqn, strlen(__NAMESPACE__));
			$include_file = $this->getInstallPath()
				. str_replace('\\', '/', $vendor_class_name)
				. $this->getScriptSuffix();

			require $include_file;
		}

		private function getScriptSuffix(): string
		{
			return self::script_suffix;
		}

		private function getInstallPath(): string
		{
			return self::install_path;
		}
	});

$app = new App;

$app->state =
	[ 'current_tab' =>
		isset($_GET['current'])
			&& in_array($_GET['current'], array_keys($app->page_list), true)
			? $_GET['current']
			:
				(
					isset($app->getConfiguration()->get()['default_page'])
						? $app->getConfiguration()->get()['default_page']
						: array_keys($app->page_list)[0]
				)
	, 'tab_list' =>
		array_keys($app->page_list)
	];

$app->run();
