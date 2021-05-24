<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class App
{
	public function __construct()
	{
		ob_start();
		$this->errors = new Errors;
	}

	public function __destruct()
	{
		/// unset to force header writing before we flush
		$this->state_controller = null;
		ob_flush();
	}

	private function runStateController(): string
	{
		$config = $this->getConfiguration();
		$this->template_engine = new Template($config);
		$this->state_controller = new StateController($this);
		return $this->state_controller->run($config);
	}

	public function run(): void
	{
		$state_view = $this->runStateController();
		$config = $this->getConfiguration();

		if(isset($_GET['page']))
		{
			$page = $_GET['page'];
			if(isset($config->getPageList()[$page]['controller']))
				$this->runPageController($config->getPageList()[$page]['controller']);
			else
				$this->errors[] = sprintf('App \'%s\' not supported', $page);
		}

		include $this->getTemplateFor('index');
	}

	public function logError(string $message): void
	{
		$this->errors[] = $message;
	}

	public function getState(): State
	{
		return $this->state_controller->getModel();
	}

	public function setState(State $new_state): void
	{
		$this->state_controller->setModel($new_state);
	}

	public function getTemplateEngine(): Template
	{
		return $this->template_engine;
	}

	public function setTemplateEngine(Template $engine): void
	{
		$this->template_engine = $engine;
	}

	public function getPageList(): array
	{
		return $this->configuration->getPageList();
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
		return $this->template_engine->getFor($name, $type);
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

	private $template_engine;
	private $state_controller;
	private $errors;
	private $configuration;
}
