<?php
namespace M18\Profitbase;

abstract class Model
{
	protected $id;

	protected $properties = array();

	public function __construct(string $id)
	{
		$this->id = $id;
	}

	public function addProperty(string $key, $value)
	{
		$this->properties[$key] = $value;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getProperties(): array
	{
		return $this->properties;
	}

	public function getProperty(string $property)
	{
		if (!isset($this->properties[$property]))
		{
			throw new \Exception('Property ' . $property . ' not found');
		}

		return $this->properties[$property];
	}
}