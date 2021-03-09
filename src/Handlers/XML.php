<?php
namespace M18\Profitbase\Handlers;

use DateTime;
use Exception;
use XMLReader;
use SimpleXMLElement;
use M18\Profitbase\Models\Utils\RoomFields;
use M18\Profitbase\Models\Building;
use M18\Profitbase\Models\Complex;
use M18\Profitbase\Models\Room;
use M18\Profitbase\Models\SpecialOffer;
use M18\Profitbase\Models\Status;
use M18\Profitbase\Models\Utils\CustomFields;
use M18\Profitbase\Models\Result\Data as ResultData;

abstract class XML
{
	public static $URL = '';

	protected static $DATETIME_FORMAT = 'Y-m-d\TH:i:sP';

	protected $reader = null;

	public function getData($resultModel = null, bool $get_values = true): ResultData
	{
		if (empty(self::$URL))
		{
			throw new Exception('No url to open');
		}

		if (is_null($resultModel))
		{
			$resultModel = new ResultData();
		}

		return $this->fetch($resultModel, $get_values);
	}

	protected function fetch(ResultData $resultModel, bool $get_values): ResultData
	{
		$this->openFileStream(self::$URL);

		// Переходим к узлу generation-date
		$this->reader->read();
		$this->reader->read();

		$generation_date_node = new SimpleXMLElement($this->reader->readOuterXml());
		$generation_date = $this->getValue($generation_date_node);

		$resultModel->setDatetime($generation_date, self::$DATETIME_FORMAT);

		// Переходим к нужному узлу offer
		while ($this->reader->read() && $this->reader->name !== 'offer') {}

		if ($get_values)
		{
			while ($this->reader->name === 'offer')
			{
				$node = new SimpleXMLElement($this->reader->readOuterXml());

				$complex_id = $this->getValue($node->object->id);
				$complex_name = $this->getValue($node->object->name);
				$complex_location = $this->getValue($node->object->location, 'array');

				$house_id = $this->getValue($node->house->id);
				$house_name = $this->getValue($node->house->name);
				$house_floors_total = $this->getValue($node->house->{'floors-total'}, 'int');
				$house_built_year = $this->getValue($node->house->{'built-year'});
				$house_ready_quarter = $this->getValue($node->house->{'ready-quarter'}, 'int');
				$house_building_state = $this->getValue($node->house->{'building-state'});

				$room_id = $this->getAttribute($node, 'internal-id');
				$section = $this->getValue($node->{'building-section'}, 'int');
				$date_booked = $this->getValue($node->{'date-booked'});
				$creation_date = $this->getValue($node->{'creation-date'});
				$last_update_date = $this->getValue($node->{'last-update-date'});

				$status_id = $this->getValue($node->status_id);
				$status_alias = $this->getValue($node->status);
				$status_humanized = $this->getValue($node->{'status-humanized'});

				if ($status_alias === 'AVAILABLE')
				{
					$resultModel->incrementEnabledRoomsCount();
				}

				if (isset($node->{'special-offers'}->{'special-offer'}))
				{
					$special_offer_node = $node->{'special-offers'}->{'special-offer'};

					$specialOffer = new SpecialOffer($special_offer_node->id);

					$specialOffer->addProperty('name', $this->getValue($special_offer_node->name));
					$specialOffer->addProperty('price', $this->getValue($special_offer_node->{'discount-price'}));
					$specialOffer->addProperty('description', $this->getValue($special_offer_node->description));
					$specialOffer->addProperty('unit', $this->getValue($special_offer_node->{'discount-unit'}));
					$specialOffer->addProperty('type', $this->getValue($special_offer_node->{'discount-type'}));
					$specialOffer->addProperty('value', $this->getValue($special_offer_node->{'value'}));
					$specialOffer->addProperty('url', $this->getValue($special_offer_node->{'url'}));
				}
				else
				{
					$specialOffer = new SpecialOffer('');
				}

				// Создание полей объекта
				$room = new Room($room_id);
				$complex = new Complex($complex_id);
				$building = new Building($house_id);
				$status = new Status($status_id);

				$complex->addProperty('name', $complex_name);
				$complex->addProperty('location', $complex_location);

				$building->addProperty('complex_id', $complex_id);
				$building->addProperty('name', $house_name);
				$building->addProperty('floors_total', $house_floors_total);
				$building->addProperty('built_year', $house_built_year);
				$building->addProperty('ready_quarter', $house_ready_quarter);
				$building->addProperty('building_state', $house_building_state);

				$status->addProperty('alias', $status_alias);
				$status->addProperty('humanized', $status_humanized);

				$resultModel->addComplex($complex);
				$resultModel->addBuilding($building);
				$resultModel->addStatus($status);
				$resultModel->addSpecialOffer($specialOffer);

				$building->addComplex($complex);

				// Помещение
				$room->addProperty('building', $building);
				$room->addProperty('section', $section);

				foreach (RoomFields::get() as $field => $var_type)
				{
					$room->addProperty($field, $this->getValue($node->{$field}, $var_type));
				}

				$room->addProperty('status', $status);
				$room->addProperty('special_offer', $specialOffer);
				$room->addProperty('booked_date', $room->getBookedDate($date_booked));

				$custom_fields = $node->{'custom-field'};

				foreach ($custom_fields as $custom_field_node)
				{
					$field_value = CustomFields::getFieldValue($custom_field_node);
					$room->addProperty($field_value['field'], $field_value['value']);
				}

				$room->addProperty('creation_date', DateTime::createFromFormat(self::$DATETIME_FORMAT, $creation_date));
				$room->addProperty('last_update_date', DateTime::createFromFormat(self::$DATETIME_FORMAT, $last_update_date));

				$resultModel->addRoom($room);

				$this->reader->next('offer');
			}
		}

		return $resultModel;
	}

	protected function getAttribute(SimpleXMLElement $node, string $attribute): string
	{
		if (isset($node[$attribute]))
		{
			return (string) $node[$attribute];
		}

		return '';
	}

	protected function getValue(SimpleXMLElement $node, string $type = 'string')
	{
		$value = $node->value ?? $node;
        
        if (isset($value[0]))
		{
			$value = $value[0];
		}
        
		switch ($type)
		{
			case 'int': return (int) $value;
			case 'float': return (float) $value;
			case 'array': return $this->xmlObjectToArray($value);
			case 'bool': return !empty((string) $value);
			case 'string':
			default:
				return (string) $node;
		}
	}

	protected function xmlObjectToArray(SimpleXMLElement $xml_object, array $out = array()): array
	{
		foreach ((array) $xml_object as $index => $node)
		{
			$out[$index] = (is_object($node)) ? $this->xmlObjectToArray($node) : $node;
		}

		return $out;
	}

	private function openFileStream(string $xml_url): void
	{
		$this->reader = new XMLReader();
		$this->reader->open($xml_url);
	}
}