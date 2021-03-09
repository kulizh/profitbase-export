<?php
namespace M18\Profitbase\Models\Result;

use DateTime;
use M18\Profitbase\Models\Room;
use M18\Profitbase\Models\Building;
use M18\Profitbase\Models\Complex;
use M18\Profitbase\Models\Status;
use M18\Profitbase\Models\SpecialOffer;

final class Data
{
	private $complexes;

	private $buildings;

	private $rooms;

	private $statuses;

	private $specialOffers;

	private $fieldsValues = array();

	private $datetime;

	private $enabledRoomsCount = 0;

	private $roomsTotal = 0;

	public function setDatetime(string $datetime, string $format)
	{
		$this->datetime = DateTime::createFromFormat($format, $datetime);
	}

	public function addComplex(Complex $complex)
	{
		$this->complexes[$complex->getId()] = $complex->getProperties();
	}

	public function addBuilding(Building $building)
	{
		$this->buildings[$building->getId()] = $building->getProperties();
	}

	public function addRoom(Room $room)
	{
		$this->rooms[$room->getId()] = $room->getProperties();
		$this->roomsTotal = count($this->rooms);
	}

	public function addBuildingToComplex(Building $building, string $complex_id)
	{
		$this->complexes[$complex_id]['buildings'][$building->getId()] = $building;
	}

	public function addStatus(Status $status)
	{
		$this->statuses[$status->getId()] = $status;
	}

	public function addSpecialOffer(SpecialOffer $specialOffer)
	{
		if (!empty($specialOffer->getId()))
		{
			$this->specialOffers[$specialOffer->getId()] = $specialOffer;
		}
	}

	public function incrementEnabledRoomsCount()
	{
		$this->enabledRoomsCount++;
	}

	public function addFieldValue(string $field, $value)
	{
		if (empty($this->fieldsValues[$field]))
		{
			$this->fieldsValues[$field] = array();
		}

		if (!in_array($value, $this->fieldsValues[$field]))
		{
			array_push($this->fieldsValues[$field], $value);
		}
	}

	public function getDatetime(): ?DateTime
	{
		return $this->datetime;
	}

	public function getComplexes(): array
	{
		return $this->complexes;
	}

	public function getBuildings(): array
	{
		return $this->buildings;
	}

	public function getRooms(): array
	{
		return $this->rooms;
	}

	public function getStatuses(): array
	{
		return $this->statuses;
	}

	public function getSpecialOffers(): array
	{
		return $this->specialOffers;
	}

	public function getFieldsValues(): array
	{
		return $this->fieldsValues;
	}

	public function getRoomsTotal(): int
	{
		return $this->roomsTotal;
	}

	public function getEnabledRoomsCount(): int
	{
		return $this->enabledRoomsCount;
	}
}