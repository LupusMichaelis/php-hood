<?php

$config =
    [ 'page_list' =>
        [ 'php-info' =>
			[ 'title' => 'PHP Infos'
			, 'controller' => function(array & $config)
				{
					phpinfo();
					die();
				}
			]
        , 'apcu-info' =>
			[ 'title' => 'APCu Infos'
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
					}
					header('Location: ./apc.php');
					die();
				}
			]
		, 'apcu-stats' =>
			[ 'title' => 'APCu stats'
			, 'controller' => function(array & $config)
				{
					header('Content-type: application/json');
					die(json_encode(apcu_cache_info()));
				}
			]
		, 'memcache-stats' =>
			[ 'title' => 'Memcache infos'
			, 'controller' =>
				function(array & $config)
				{
					$class_exists = class_exists('\Memcache');
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

					header('Content-type: application/json');
					die(json_encode(compact('class_exists', 'is_instanciated', 'connections', 'stats')));
				}
			]
        ]
    , 'apc' =>
        [ 'provided-monitor' => '/usr/share/php7/apcu/apc.php'
        ]
    , 'memcache' =>
        [ [ 'localhost', 11211 ]
        ]
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
        <li id='php-info' class='handle'>PHP Infos
          <i class='reloader'>&#128472;</i></li>
        <li id='apcu-info' class='handle'>APCu Infos
          <i class='reloader'>&#128472;</i></li>
        <li id='apcu-stats' class='handle'>APCu stats
          <i class='reloader'>&#128472;</i>
          <i class='inspector'>&rdca;</i></li>
        <li class='handle'>Memcache stats
          <i class='reloader'>&#128472;</i>
          <i class='inspector'>&rdca;</i></li>
      </ol>
    </nav>

    <iframe class='hidden' src='?page=php-info'></iframe>
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
