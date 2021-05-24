<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class Template
{
	const suffix_to_mime_list =
		[ 'html' => 'text/html'
		, 'json' => 'application/json'
		];

	public function __construct(Config $config)
	{
		$this->template_path = $config->getTemplatePath();
	}

	public function getFor($name, $type='html'): ?string
	{
		if(!isset(self::suffix_to_mime_list[$type]))
		{
			$this->errors[] = sprintf('Can\'t find requested filetype (%s|%s)', $type);
			return null;
		}

		return sprintf
			( '%s/%s.%s.php'
			, $this->template_path
			, $name
			, $type
			);
	}

	private $template_path;
}
