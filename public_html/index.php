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

$app =
    [ 'page_list' =>
        [ 'php-info' =>
			[ 'title' => 'PHP Infos'
			, 'feature_list' => ['reloader']
			, 'controller' => function(array & $app)
				{
					phpinfo();
					die();
				}
			]
        , 'apcu-info' =>
			[ 'title' => 'APCu Infos'
			, 'feature_list' => ['reloader']
			, 'controller' => function(array & $app)
				{
					if(file_exists('apc.php'))
					{
						header('Location: ./apc.php');
						die();
					}

					if(!isset($app['config']['apc']['provided-monitor']))
					{
						header('HTTP/1.1 404 Not found');
						header('Content-type: text/plain');
						die('APCu hood not configured');
					}

					$apc_scriptname = $app['config']['apc']['provided-monitor'];

					$copied = @copy($apc_scriptname, 'apc.php');

					if(!$copied)
					{
						$app['errors'][] = sprintf
							( 'Couldn\'t copy \'%s\' because \'%s\''
							, $app['config']['apc']['provided-monitor']
							, error_get_last()['message']
							);

						header('HTTP/1.1 404 Not found');
						header('Content-type: text/plain');
						die(sprintf('File \'%s\' not found', 'apc.php'));
					}

					header('Location: ./apc.php');
					die();
				}
			]
		, 'apcu-stats' =>
			[ 'title' => 'APCu stats'
			, 'feature_list' => ['reloader', 'inspector']
			, 'controller' => function(array & $app)
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

					function __invoke(array & $app)
					{
						$this->config = $app['config'];

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
        ]
	];

$app['errors'] = [];
$app['config'] = (new Config($app['errors']))->get();
$app['state'] =
	[ 'current_page' =>
		isset($_GET['current'])
			&& in_array($_GET['current'], array_keys($app['page_list']), true)
			? $_GET['current']
			:
				(
					isset($app['config']['default_page'])
						? $app['config']['default_page']
						: array_keys($app['page_list'])[0]
				)
	, 'page_list' =>
		array_keys($app['page_list'])
	];

if(isset($_GET['page']))
{
	$page = $_GET['page'];
	if(isset($app['page_list'][$page]['controller']))
		$app['page_list'][$page]['controller']($app);
	else
		$app['errors'][] = sprintf('Page \'%s\' not supported', $page);
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Under the hood of <?= htmlentities($_SERVER['SERVER_NAME']) ?></title>
    <meta name='viewport' content='width=device-width' />
    <link rel='stylesheet'
          type='text/css'
          href='hood.css'
          defer
          />
    <script type='application/javascript'>
      const state = <?= json_encode($app['state']) ?>;
      window.addEventListener('load', () => { hood(state); });
    </script>
  </head>
  <body>
<?php if(count($app['errors'])): ?>
    <div class='error'>
      <span>Errors occurred:</span>
<?php   foreach($app['errors'] as $error): ?>
      <ul>
        <li><?= htmlentities($error) ?></li>
      </ul>
    </div>
<?php   endforeach ?>
<?php endif ?>
    <nav>
      <ol>
<?php foreach($app['page_list'] as $page_id => $page_config): ?>
        <li id='<?= htmlentities($page_id, ENT_QUOTES) ?>'
            class='handle
<?php   if($app['state']['current_page'] === $page_id): ?>
                   selected
<?php   endif ?>
            '
            ><a href='?current=<?= htmlentities($page_id) ?>'>
              <i class='remover'>&cross;</i>
              <?= htmlentities($page_config['title']) ?>
<?php   if(!empty($page_config['feature_list'])): ?>
<?php     foreach($page_config['feature_list'] as $feature): ?>
<?php       if('reloader' === $feature): ?>
              <i class='reloader'>&#128472;</i>
<?php       endif ?>
<?php       if('inspector' === $feature): ?>
          <i class='inspector'>&neArr;</i>
<?php       endif ?>
<?php     endforeach ?>
<?php   endif ?>
          </a>
        </li>
<?php endforeach ?>
      </ol>
    </nav>

<?php foreach($app['page_list'] as $page_id => $page_config): ?>
    <iframe src='?page=<?= htmlentities($page_id, ENT_QUOTES) ?>'
<?php   if(@$app['state']['current_page'] !== $page_id): ?>
            class='hidden'
<?php   endif ?>
            ></iframe>
<?php endforeach ?>

    <div class='hidden'>
      <script defer
              type='application/javascript'
              src='hood.js'
              ></script>
    </div>
  </body>
</html>
