<?php declare(strict_types=1);

namespace LupusMichaelis\PHPHood;

class State
	implements \JsonSerializable
	, \ArrayAccess
{
	public function __construct(array $initial_state)
	{
		$this->set($initial_state);
	}

	public function set(array $state)
	{
		$this->actual = $state;
	}

	public function isValid(): bool
	{
		return (bool) count($this->actual);
	}

	/** @see \ArrayAccess::offsetExists */
	public function offsetExists($offset): bool
	{
		return isset($this->actual[$offset]);
	}

	/** @see \ArrayAccess::offsetGet */
	public function &offsetGet($offset)
	{
		return $this->actual[$offset];
	}

	/** @see \ArrayAccess::offsetSet */
	public function offsetSet($offset, $value): void
	{
		$this->actual[$offset] = $value;
	}

	/** @see \ArrayAccess::offsetUnset */
	public function offsetUnset($offset): void
	{
		unset($this->actual[$offset]);
	}

	/** @see \JsonSerializable::jsonSerialize */
	public function jsonSerialize()
	{
		return (array) $this->actual;
	}

	private $actual =
		[ 'current_tab' => ''
		, 'tab_list' => []
		, 'feature_list' => []
		];
}
