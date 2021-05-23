<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class App
{
	const suffix_to_mime_list =
		[ 'html' => 'text/html'
		, 'json' => 'application/json'
		];

	public function __construct()
	{
		ob_start();
		$this->errors = new Errors;
	}

	public function __destruct()
	{
		$this->saveStateToCookie();
		ob_flush();
	}

	public function run(): void
	{
		$config = $this->getConfiguration();
		$this->fetchStateFromCookie();
		if(empty($this->state))
		{
			$state = new State(
				[ 'current_tab' =>
					isset($_GET['current'])
						&& in_array($_GET['current'], array_keys($this->page_list), true)
						? $_GET['current']
						:
							(
								isset($config->get()['default_page'])
									? $config->get()['default_page']
									: array_keys($this->page_list)[0]
							)
				, 'tab_list' =>
					[ key($this->page_list) ]
				, 'feature_list' =>
					array_keys($this->page_list)
				]);
			$this->setState($state);
		}

		if(isset($_GET['page']))
		{
			$page = $_GET['page'];
			if(isset($this->page_list[$page]['controller']))
				$this->runPageController($this->page_list[$page]['controller']);
			else
				$this->errors[] = sprintf('App \'%s\' not supported', $page);
		}

		if(isset($_GET['add-tab']))
		{
			ob_start();
			$success = include $this->getTemplateFor('add-tab-form');

			if(false === $success)
				ob_flush();
			else
				$form = ob_get_clean();
		}

		if(isset($_POST['add-tab']))
		{
			$page_id = $_POST['add-tab'];
			if(isset($this->page_list[$page_id]))
				$this->state['tab_list'][] = $_POST['add-tab'];
			else
				$this->errors[] = sprintf('Unkown page \'%s\'', $page_id);

			unset($page_id);
		}

		include $this->getTemplateFor('index');
	}

	public function logError(string $message): void
	{
		$this->errors[] = $message;
	}

	public function getState(): State
	{
		return clone $this->state;
	}

	public function setState(State $new_state): void
	{
		$this->state = clone $new_state;
	}

	public function getPageList(): array
	{
		return $this->page_list;
	}

	private function runPageController($controller): void
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

	private function saveStateToCookie()
	{
		$cookie = sprintf('Set-Cookie: %s=%s; Expires=%s; SameSite=Lax; Path=/'
			, __class__
			, urlencode(json_encode($this->state))
			, gmdate('D, d M Y H:i:s T', time() + 365 * 3600)
			);

		header($cookie);
	}

	private function fetchStateFromCookie()
	{
		$state = \json_decode($_COOKIE[__CLASS__], true);
		$this->state = new State($state);
	}

	private $page_list =
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
	private $state;
	private $errors;
	private $configuration;
}
