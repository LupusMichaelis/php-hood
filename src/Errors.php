<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class Errors
	extends \ArrayObject
{
	public function offsetSet($offset, $value): void
	{
		parent::offsetSet($offset, $value);
		error_log(var_export($value, true));
	}
}
