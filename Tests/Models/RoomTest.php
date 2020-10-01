<?php
namespace Models;

use M18\Profitbase\Models\Room;
use PHPUnit\Framework\TestCase;

class RoomTest extends TestCase
{
	private $roomInstance = null;

	private $testId = 12;

	protected function setUp(): void
	{
		$this->roomInstance = new Room($this->testId);
	}

	public function testIsNewFlat()
	{
		$this->assertTrue($this->roomInstance->isNewFlat(true));
		$this->assertTrue($this->roomInstance->isNewFlat('true'));
		$this->assertTrue($this->roomInstance->isNewFlat(1));

		$this->assertFalse($this->roomInstance->isNewFlat(false));
		$this->assertFalse($this->roomInstance->isNewFlat(''));
		$this->assertFalse($this->roomInstance->isNewFlat(0));
	}

	public function testIsStudio()
	{
		$this->assertTrue($this->roomInstance->isStudio(true));
		$this->assertTrue($this->roomInstance->isStudio('true'));
		$this->assertTrue($this->roomInstance->isStudio(1));

		$this->assertFalse($this->roomInstance->isStudio(false));
		$this->assertFalse($this->roomInstance->isStudio(''));
		$this->assertFalse($this->roomInstance->isStudio(0));
	}

	public function testGetBookedDate()
	{
		$this->assertEmpty($this->roomInstance->getBookedDate(''));
		$this->assertEquals('2020-20-20', $this->roomInstance->getBookedDate('2020-20-20'));

		self::assertInstanceOf(\DateTime::class, $this->roomInstance->getBookedDate('10.05.20'));
	}
}
