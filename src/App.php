<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class App
{
	const suffix_to_mime_list =
		[ 'html' => 'text/html'
		, 'json' => 'application/json'
		];

	public function run(): void
	{
		$config = $this->getConfiguration();

		if(isset($_GET['page']))
		{
			$page = $_GET['page'];
			if(isset($this->page_list[$page]['controller']))
				$this->runController($this->page_list[$page]['controller']);
			else
				$this->errors[] = sprintf('App \'%s\' not supported', $page);
		}

		include $this->getTemplateFor('index');
	}

	public function logError(string $message): void
	{
		$this->errors[] = $message;
	}

	private function runController($controller): void
	{
		if(is_callable($controller))
		{
			$controller($this);
			return;
		}

		if(is_string($controller) && class_exists($controller))
		{
			(new $controller)($this);
			return;
		}

		throw new \Exception('Improper controller');
	}

	public function getTemplateFor($name, $type='html'): ?string
	{
		if(!isset(self::suffix_to_mime_list[$type]))
		{
			$this->errors[] = sprintf('Can\'t find requested filetype (%s|%s)', $type);
			return null;
		}

		$config = $this->getConfiguration();
		return sprintf
			( '%s/%s.%s.php'
			, $config->getTemplatePath()
			, $name
			, $type
			);
	}

	public function getConfiguration(): Config
	{
		if(!isset($this->configuration))
		{
			$this->configuration = new Config;
			$this->configuration->bindErrors($this->errors);
		}

		return $this->configuration;
	}

	public $page_list = [];
	public $state = [];
	private $errors;
	private $configuration;

	public function __construct()
	{
		$this->errors = new Errors;

		$this->page_list =
			[ 'php-info' =>
				[ 'title' => 'PHP Infos'
				, 'feature_list' => ['reloader']
				, 'controller' => [ Controllers\BasicPage::class, 'PHPInfo' ]
				]
			, 'apcu-info' =>
				[ 'title' => 'APCu Infos'
				, 'feature_list' => ['reloader']
				, 'controller' => Controllers\ApcPage::class
				]
			, 'apcu-stats' =>
				[ 'title' => 'APCu stats'
				, 'feature_list' => ['reloader', 'inspector']
				, 'controller' => [ Controllers\BasicPage::class, 'APCUStats' ]
				]
			, 'memcached-stats' =>
				[ 'title' => 'Memcache infos'
				, 'feature_list' => ['reloader', 'inspector']
				, 'controller' => Controllers\MemcachedStatsPage::class
				]
			];
	}
}
