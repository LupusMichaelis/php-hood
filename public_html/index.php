<?php

$config =
    [ 'page_list' =>
        [ 'php-info' =>
			[ 'title' => 'PHP Infos'
			, 'feature_list' => ['reloader']
			, 'controller' => function(array & $config)
				{
					phpinfo();
					die();
				}
			]
        , 'apcu-info' =>
			[ 'title' => 'APCu Infos'
			, 'feature_list' => ['reloader']
			, 'controller' => function(array & $config)
				{
					if(!file_exists('apc.php'))
					{
						$copied = @copy($config['apc']['provided-monitor'], 'apc.php');

						if(!$copied)
							$errors[] = sprintf
								( 'Couldn\'t copy \'%s\' because \'%s\''
								, $config['apc']['provided-monitor']
								, error_get_last()['message']
								);

						header('HTTP/1.1 404 Not found');
						header('Content-type: text/plain');
						die(sprintf('File \'%s\' not found', 'apc.php'));
					}
					else
					{
						header('Location: ./apc.php');
						die();
					}
				}
			]
		, 'apcu-stats' =>
			[ 'title' => 'APCu stats'
			, 'feature_list' => ['reloader', 'inspector']
			, 'controller' => function(array & $config)
				{
					header('Content-type: application/json');

					if(function_exists('\apcu_cache_info'))
						die(json_encode(apcu_cache_info()));

					die(json_encode(['error' => 'Function \'\\apcu_cache_info\' doesn\'t exist']));
				}
			]
		, 'memcache-stats' =>
			[ 'title' => 'Memcache infos'
			, 'feature_list' => ['reloader', 'inspector']
			, 'controller' => function(array & $config)
				{
					[ $is_instanciated, $connections, $stats ] = [ null, null, null];

					$class_exists = class_exists('\Memcache');

					if($class_exists)
					{
						$con = new Memcache;
						$is_instanciated = (bool) $con;

						$connections = [];
						if($con)
						{
							foreach($config['memcache'] as [$host, $port])
								$connections["$host:$port"] =
									@$con->connect($host, $port)
										? 'succeed'
										: 'failed'
										;
						}

						$stats = @$con->getStats();
						$con->close();
					}

					header('Content-type: application/json');
					die(json_encode(compact('class_exists', 'is_instanciated', 'connections', 'stats')));
				}
			]
        ]
	, 'default_page' => 'apcu-info'
	, 'apc' =>
		[ 'provided-monitor' => '/usr/share/php7/apcu/apc.php'
		]
	, 'memcache' =>
		[ [ 'localhost', 11211 ]
		, [ 'cache', 11211 ]
		]
	];

$state =
	[ 'current_page' =>
		isset($_GET['current'])
			&& in_array($_GET['current'], array_keys($config['page_list']), true)
			? $_GET['current']
			:
				(
					isset($config['default_page'])
						? $config['default_page']
						: array_keys($config['page_list'])[0]
				)
	, 'page_list' =>
		array_keys($config['page_list'])
	];
$errors = [];

if(isset($_GET['page']))
{
	$page = $_GET['page'];
	if(isset($config['page_list'][$page]['controller']))
		$config['page_list'][$page]['controller']($config);
	else
		$errors[] = sprintf('Page \'%s\' not supported', $page);
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
      const state = <?= json_encode($state) ?>;
      window.addEventListener('load', () => { hood(state); });
    </script>
  </head>
  <body>
<?php if(count($errors)): ?>
    <div class='error'>
      <span>Errors occurred:</span>
<?php   foreach($errors as $error): ?>
      <ul>
        <li><?= htmlentities($error) ?></li>
      </ul>
    </div>
<?php   endforeach ?>
<?php endif ?>
    <nav>
      <ol>
<?php foreach($config['page_list'] as $page_id => $page_config): ?>
        <li id='<?= htmlentities($page_id, ENT_QUOTES) ?>'
            class='handle
<?php   if($state['current_page'] === $page_id): ?>
                   selected
<?php   endif ?>
            '
            ><?= htmlentities($page_config['title']) ?>
<?php   if(!empty($page_config['feature_list'])): ?>
<?php     foreach($page_config['feature_list'] as $feature): ?>
<?php       if('reloader' === $feature): ?>
              <i class='reloader'>&#128472;</i>
<?php       endif ?>
<?php       if('inspector' === $feature): ?>
          <i class='inspector'>&rdca;</i>
<?php       endif ?>
<?php     endforeach ?>
<?php   endif ?>
        </li>
<?php endforeach ?>
      </ol>
    </nav>

<?php foreach($config['page_list'] as $page_id => $page_config): ?>
    <iframe src='?page=<?= htmlentities($page_id, ENT_QUOTES) ?>'
<?php   if(@$state['current_page'] !== $page_id): ?>
            class='hidden'
<?php   endif ?>
            ></iframe>
<?php endforeach ?>
    <iframe class='hidden' src='?page=apcu-info'></iframe>
    <iframe class='hidden' src='?page=apcu-stats'></iframe>
    <iframe class='hidden' src='?page=memcache-stats'></iframe>

    <div class='hidden'>
      <script defer
              type='application/javascript'
              src='hood.js'
              ></script>
    </div>
  </body>
</html>
