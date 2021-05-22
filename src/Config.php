<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class Config
{
	const default_path_config =
		[ 'template-path' =>
			[ 'value' => '../templates'
			, 'environment' => 'HOOD_TEMPLATE_PATH'
			]
		, 'dist-filename' =>
			[ 'value' => '../config.php-dist'
			, 'environment' => 'HOOD_DIST_CONFIG'
			]
		, 'filename' =>
			[ 'value' => '../config.php'
			, 'environment' => 'HOOD_CONFIG'
			]
		];

	public function __construct()
	{
		$this->errors = new Errors;
	}

	public function bindErrors(Errors $errors): void
	{
		if(count($this->errors))
			$this->errors[] = 'Errors are being ignored';

		$this->errors = $errors;
	}

	public function load(): bool
	{
		return $this->figure_out('filename')
			&& $this->load_file()
			|| $this->create_and_load();
	}

	public function &get(): array
	{
		if(empty($this->actual))
			$this->load();

		return $this->actual;
	}

	public function getTemplatePath(): string
	{
		if(!$this->figure_out('template-path'))
		{
			$e = 'Can\'t find where to look for templates';
			$this->errors[] = $e;
			throw new \Exception($e);
		}

		return $this->file_list['template-path'];
	}

	private function figure_out(string $key): bool
	{
		$this->file_list[$key] = $this->value_or_default($key);
		return $this->check_localness($this->file_list[$key]);
	}

	private function value_or_default(string $what): string
	{
		if(!isset(self::default_path_config[$what]))
			throw new \Exception(sprintf('No defaults for \'%s\'', $what));

		['environment' => $environment, 'value' => $value ] = self::default_path_config[$what];

		return isset($_ENV[$environment])
			? $_ENV[$environment]
			: $value;
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
		if(!$this->figure_out('filename'))
		{
			$this->errors[] = 'Can\'t find where to look for config file';
			return false;
		}

		$config = @include $this->file_list['filename'];

		if(empty($config))
		{
			$this->errors[] = sprintf('Couldn\'t load %s', $this->file_list['filename']);
			return false;
		}

		$this->actual = $config;

		return true;
	}

	private function create_and_load(): bool
	{
		if(!$this->figure_out('dist-filename'))
		{
			$this->errors[] = 'Can\'t find where to look for distribution config file';
			return false;
		}

		$copied = @copy($this->file_list['dist-filename'], $this->file_list['filename']);
		if(!$copied)
		{
			$this->errors[] = error_get_last();
			return false;
		}

		$config = @include $config_file;

		if(empty($config))
		{
			$this->errors[] = error_get_last();
			return false;
		}

		$this->actual = $config;
	}


	private $file_list =
		[ 'config-filename' => null
		, 'dist-filename' => null
		, 'template-path' => null
		];
	private $actual = [];
	private $errors;
}
