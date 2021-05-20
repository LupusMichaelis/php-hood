<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class Config
{
	const default_filename = '../config.php';
	const default_dist_filename = '../config.php-dist';

	public function __construct(array & $e)
	{
		$this->error_collector = $e;
	}

	public function load(): bool
	{
		return $this->figure_filename_out()
			&& $this->load_file()
			|| $this->create_and_load();
	}

	public function &get(): array
	{
		if(empty($this->config))
			$this->load();

		return $this->config;
	}

	private function figure_filename_out(): bool
	{
		$this->config_filename = isset($_ENV['HOOD_CONFIG'])
			? $_ENV['HOOD_CONFIG']
			: self::default_filename;

		return $this->check_localness($this->config_filename);
	}

	private function figure_dist_filename_out(): bool
	{
		$this->dist_filename = isset($_ENV['HOOD_DIST_CONFIG'])
			? $_ENV['HOOD_DIST_CONFIG']
			: self::default_dist_filename;

		return $this->check_localness($this->dist_filename);
	}

	private function check_localness(string $filename): bool
	{
		return false
			// protocol scheme can't get `.` or `/` in them
			|| 0 === strpos($filename, '.')
			|| 0 === strpos($filename, '/')
			// defitively local
			|| 0 === strpos($filename, 'file://')
			|| 0 === strpos($filename, 'phar://')
			|| 0 === strpos($filename, 'data://')
			// possibly remote
			|| false === strpos($filename, '://')
			;
	}

	private function load_file(): bool
	{
		$config = @include $this->config_filename;

		if(empty($config))
			return false;

		$this->config = $config;

		return true;
	}

	private function create_and_load(): bool
	{
		if(!$this->figure_dist_filename_out())
		{
			$this->error_collector[] = 'Can\'t find where to look for distribution config file';
			return false;
		}

		$copied = @copy($this->dist_filename, $this->config_filename);
		if(!$copied)
		{
			$this->error_collector[] = error_get_last();
			return false;
		}

		$config = @include $config_file;

		if(empty($config))
		{
			$this->error_collector[] = error_get_last();
			return false;
		}

		$this->config = $config;
	}

	private $config_filename = self::default_filename;
	private $config = [];
	private $error_collector = [];
}
