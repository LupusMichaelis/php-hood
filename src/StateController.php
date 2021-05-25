<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class StateController
{
	const cookie_name = 'php-hood-state';

	public function __construct(App $app)
	{
		$this->app = $app;
		$this->model = new State([]);
	}

	public function __destruct()
	{
		$this->saveToCookie();
	}

	public function run(Config $config): string
	{
		$view = [];

		if(!$this->fetchFromCookie())
			$this->setFromConfiguration($config);

		if(isset($_GET['select-tab'])
			&& in_array($_GET['select-tab'], array_keys($config->getPageList()), true))
			$this->model['current_tab'] = $_GET['select-tab'];

		if(isset($_GET['add-tab']))
		{
			ob_start();
			$success = include $this->app->getTemplateEngine()->getFor('add-tab-form');

			if(false === $success)
				throw new \Exception('');
			else
				$view[] = ob_get_clean();
		}

		if(isset($_POST['add-tab']))
		{
			$page_id = $_POST['add-tab'];
			if(!isset($config->getPageList()[$page_id]))
				throw new \Exception(sprintf('Unkown tab \'%s\'', $page_id));

			$this->model['tab_list'][] = $_POST['add-tab'];
			$view[] = '<div class=\'info\'>Tab added</div>';
		}

		return implode("\n", $view);
	}

	public function setFromConfiguration(Config $config)
	{
		if(!$this->model->isValid())
		{
			$state = new State(
				[ 'current_tab' =>
							(
								isset($config->get()['default_page'])
									? $config->get()['default_page']
									: array_keys($config->getPageList())[0]
							)
				, 'tab_list' =>
					[ key($config->getPageList()) ]
				, 'feature_list' =>
					array_keys($config->getPageList())
				]);
			$this->setModel($state);
		}
	}

	public function saveToCookie()
	{
		$cookie = sprintf('Set-Cookie: %s=%s; Expires=%s; SameSite=Lax; Path=/'
			, static::cookie_name
			, urlencode(json_encode($this->model))
			, gmdate('D, d M Y H:i:s T', time() + 365 * 3600)
			);

		header($cookie);
	}

	public function fetchFromCookie(): bool
	{
		if(!isset($_COOKIE[static::cookie_name]))
			return false;

		$state = \json_decode($_COOKIE[static::cookie_name], /* associative = */true);
		$this->model = new State($state ?: []);

		return $this->model->isValid();
	}

	public function setModel(State $state): void
	{
		$this->model = clone $state;
	}

	public function getModel(): State
	{
		return clone $this->model;
	}

	private $app;
	private $model;
}
