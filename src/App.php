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
				, 'controller' => function(App $app): void
					{
						phpinfo();
						die();
					}
				]
			, 'apcu-info' =>
				[ 'title' => 'APCu Infos'
				, 'feature_list' => ['reloader']
				, 'controller' => Controllers\ApcPage::class
				]
			, 'apcu-stats' =>
				[ 'title' => 'APCu stats'
				, 'feature_list' => ['reloader', 'inspector']
				, 'controller' => function(App $app)
					{
						header('Content-type: application/json');

						if(function_exists('\apcu_cache_info'))
							die(json_encode(apcu_cache_info()));

						die(json_encode(['error' => 'Function \'\\apcu_cache_info\' doesn\'t exist']));
					}
				]
			, 'memcached-stats' =>
				[ 'title' => 'Memcache infos'
				, 'feature_list' => ['reloader', 'inspector']
				, 'controller' => new class
					{
						var $app = [];

						function __invoke(App $app): void
						{
							$this->config = $app->getConfiguration()->get();

							$payload = [];
							foreach
								(
									[ 'memcache' => 'check_memcache'
									, 'memcached' => 'check_memcached'
									] as $extension => $method
								)
								$payload[$extension] = $this->$method();

							header('Content-type: application/json');
							die(json_encode($payload));
						}

						function check_memcache()
						{
							[ $is_instanciated, $connections, $stats ] = [ null, null, []];
							$class_exists = class_exists('\Memcache');

							if($class_exists)
							{
								$con = new \Memcache;
								$is_instanciated = (bool) $con;

								$connections = [];
								if($con)
								{
									foreach($this->config['memcached'] as [$host, $port])
									{
										$host_port = "$host:$port";
										$connections[$host_port] =
											@$con->connect($host, $port)
												? 'succeed'
												: 'failed'
												;

										$stats[$host_port] = $con->getStats();
									}

									$con->close();
								}

								$stats[] = @$con->getStats();
								unset($con);
							}

							return compact
								( 'class_exists'
								, 'is_instanciated'
								, 'connections'
								, 'stats'
								);
						}

						function check_memcached()
						{
							[ $is_instanciated, $connections, $stats, $beacon ] = [ null, null, [], []];
							$class_exists = class_exists('\Memcached');
							$is_configured = isset($this->config['memcached']) ? true : false;

							if($class_exists && $is_configured)
							{
								$con = new \Memcached;
								$is_instanciated = (bool) $con;

								$connections = [];
								if($con)
								{
									foreach($this->config['memcached'] as [$host, $port])
									{
										$host_port = "$host:$port";
										$connections[$host_port] =
											@$con->addServer($host, $port)
												? 'succeed'
												: 'failed'
												;

										$beacon[$host_port] = $this->beacon($con);
										$stats[$host_port] = $con->getStats();

										// If an added server's failing, the whole pool is
										// failing and no stat can be gathered
										$con->resetServerList();
									}
								}

								$stats[] = @$con->getStats();
								unset($con);
							}
							return compact
								( 'class_exists'
								, 'is_configured'
								, 'is_instanciated'
								, 'connections'
								, 'stats'
								, 'beacon'
								);
						}

						function beacon(\Memcached $con, $beacon = 0)
						{
							$con->add('beacon', $beacon)
								or $beacon = $con->increment('beacon')
								or $con->set('beacon', $beacon);

							return $beacon;
						}
					}
				]
			];
	}
}
