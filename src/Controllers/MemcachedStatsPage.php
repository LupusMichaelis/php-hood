<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood\Controllers;

use \LupusMichaelis\PHPHood\App;

class MemcachedStatsPage
	implements PageInterface
{
	public function __invoke(App $app): void
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

	private function check_memcache()
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

	private function check_memcached()
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

	private function beacon(\Memcached $con, $beacon = 0)
	{
		$con->add('beacon', $beacon)
			or $beacon = $con->increment('beacon')
			or $con->set('beacon', $beacon);

		return $beacon;
	}
}
