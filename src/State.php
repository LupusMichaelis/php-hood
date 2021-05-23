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

	// implements \ArrayAccess
	public function offsetExists($offset): bool
	{
		return isset($this->actual[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->actual[$offset];
	}

	public function offsetSet($offset, $value): void
	{
		$this->actual[$offset] = $value;
	}

	public function offsetUnset($offset): void
	{
		unset($this->actual[$offset]);
	}

	// implements \JsonSerializable
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
