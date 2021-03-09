<?php

use M18\Profitbase\AbstractModel as Model;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
	protected $newAnonymousClassFromAbstract;

	private $testId = 12;

	protected function setUp(): void
	{
		$this->newAnonymousClassFromAbstract = new class($this->testId) extends Model {
			public function returnThis()
			{
				return $this;
			}
		};
	}

	public function testAbstractClassMethod()
	{
		$this->assertInstanceOf(
			Model::class,
			$this->newAnonymousClassFromAbstract->returnThis()
		);
	}

	public function testAnonymousClassMethod()
	{
		$this->assertInstanceOf(
			Model::class,
			$this->newAnonymousClassFromAbstract->returnThis()
		);
	}

	public function testGetId()
	{
		$this->assertEquals($this->testId, $this->newAnonymousClassFromAbstract->getId());
	}

	public function testAddProperty()
	{
		$property_key = 'key';
		$property_value = 'value';

		$this->newAnonymousClassFromAbstract->addProperty($property_key, $property_value);

		$this->assertEquals(
			$property_value,
		 	$this->newAnonymousClassFromAbstract->getProperty($property_key));
	}

	public function testGetProperties()
	{
		$property_key = 'key';
		$property_value = 'value';

		$property_key_2 = 'key_1';
		$property_value_2 = 'value_2';

		$this->newAnonymousClassFromAbstract->addProperty($property_key, $property_value);
		$this->newAnonymousClassFromAbstract->addProperty($property_key_2, $property_value_2);

		$this->assertEquals(
			array(
				$property_key => $property_value,
				$property_key_2 => $property_value_2
			),
			$this->newAnonymousClassFromAbstract->getProperties());
	}
}
