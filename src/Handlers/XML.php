<?php
namespace M18\Profitbase\Handlers;

use DateTime;
use XMLReader;
use Exception;
use SimpleXMLElement;
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
		switch ($type)
		{
			case 'int': return (int) $node;
			case 'float': return (float) $node;
			case 'array': return $this->xmlObjectToArray($node);
			case 'string':
			default:
				return (string) $node;
		}
	}

	protected function xmlObjectToArray(SimpleXMLElement $xml_object, array $out = array()): array
	{
		foreach ((array) $xml_object as $index => $node )
		{
			$out[$index] = ( is_object ( $node ) ) ? $this->xmlObjectToArray ( $node ) : $node;
		}

		return $out;
	}

	protected function fetch(ResultData $resultModel, bool $get_values): ResultData
	{
		$this->openFileStream(self::$URL);

		// Переходим к узлу generation-date считываем дату генерации:
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
				$house_floors_total = $this->getValue($node->house->{'floors-total'}, 'int');
				$house_name = $this->getValue($node->house->name);
				$house_built_year = $this->getValue($node->house->{'built-year'});
				$house_ready_quarter = $this->getValue($node->house->{'ready-quarter'}, 'int');
				$house_building_state = $this->getValue($node->house->{'building-state'});

				$room_id = $this->getAttribute($node, 'internal-id');
				$creation_date = $this->getValue($node->{'creation-date'});
				$last_update_date = $this->getValue($node->{'last-update-date'});
				$new_flat = $this->getValue($node->{'new-flat'});
				$price = $this->getValue($node->price->value, 'int');
				$area = $this->getValue($node->area->value, 'float');
				$number = $this->getValue($node->number);
				$layout_code = $this->getValue($node->{'layout-code'});
				$preset_code = $this->getValue($node->{'preset-code'});
				$price_for_meter = $this->getValue($node->{'price-meter'}->value);
				$image = $this->getValue($node->image);
				$studio = $this->getValue($node->studio);
				$open_plan = $this->getValue($node->{'open-plan'}, 'int');
				$euro_layout = $this->getValue($node->{'euro-layout'}, 'int');
				$rooms_count = $this->getValue($node->rooms, 'int');
				$floor = $this->getValue($node->floor, 'int');
				$section = $this->getValue($node->{'building-section'}, 'int');
				$description = $this->getValue($node->description);
				$date_booked = $this->getValue($node->{'date-booked'});
				$position_on_floor = $this->getValue($node->{'position-on-floor'});
				$balcony_count = $this->getValue($node->{'balcony-count'});
				$loggia_count = $this->getValue($node->{'loggia-count'});
				$separated_bathroom_unit = $this->getValue($node->{'separated-bathroom-unit'});
				$combined_bathroom_unit = $this->getValue($node->{'combined-bathroom-unit'});
				$window_view = $this->getValue($node->{'window-view'});
				$facing = $this->getValue($node->{'facing'});

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
					$special_offer_id = $special_offer_node->id;
					$special_offer_name = $this->getValue($special_offer_node->name);
					$special_offer_price = $this->getValue($special_offer_node->{'discount-price'});
					$special_offer_description = $this->getValue($special_offer_node->description);
					$special_offer_unit = $this->getValue($special_offer_node->{'discount-unit'});
					$special_offer_type = $this->getValue($special_offer_node->{'discount-type'});
					$special_offer_value = $this->getValue($special_offer_node->{'value'});
					$special_offer_url = $this->getValue($special_offer_node->{'url'});

					$specialOffer = new SpecialOffer($special_offer_id);

					$specialOffer->addProperty('name', $special_offer_name);
					$specialOffer->addProperty('price', $special_offer_price);
					$specialOffer->addProperty('description', $special_offer_description);
					$specialOffer->addProperty('unit', $special_offer_unit);
					$specialOffer->addProperty('type', $special_offer_type);
					$specialOffer->addProperty('value', $special_offer_value);
					$specialOffer->addProperty('url', $special_offer_url);
				}
				else
				{
					$specialOffer = new SpecialOffer('');
				}

				$room = new Room($room_id);
				$complex = new Complex($complex_id);
				$building = new Building($house_id);
				$status = new Status($status_id);

				$complex->addProperty('name', $complex_name);
				$complex->addProperty('location', $complex_location);

				$building->addProperty('name', $house_name);
				$building->addProperty('floors_total', $house_floors_total);
				$building->addProperty('built_year', $house_built_year);
				$building->addProperty('ready_quarter', $house_ready_quarter);
				$building->addProperty('building_state', $house_building_state);

				$status->addProperty('alias', $status_alias);
				$status->addProperty('humanized', $status_humanized);

				$resultModel->addComplex($complex);
				$resultModel->addBuildingToComplex($building, $complex_id);
				$resultModel->addBuilding($building);
				$resultModel->addStatus($status);
				$resultModel->addSpecialOffer($specialOffer);

				$building->addComplex($complex);

				// Помещение
				$room->addProperty('building', $building);
				$room->addProperty('section', $section);
				$room->addProperty('is_new', $room->isNewFlat($new_flat));
				$room->addProperty('price', $price);
				$room->addProperty('area', $area);
				$room->addProperty('number', $number);
				$room->addProperty('layout_code', $layout_code);
				$room->addProperty('preset_code', $preset_code);
				$room->addProperty('price_for_meter', $price_for_meter);
				$room->addProperty('image', $image);
				$room->addProperty('studio', $room->isStudio($studio));
				$room->addProperty('open_plan', $open_plan);
				$room->addProperty('euro_layout', $euro_layout);
				$room->addProperty('rooms_count', $rooms_count);
				$room->addProperty('floor', $floor);
				$room->addProperty('status', $status);
				$room->addProperty('booked_date', $room->getBookedDate($date_booked));
				$room->addProperty('description', $description);
				$room->addProperty('special_offer', $specialOffer);

				$custom_fields = $node->{'custom-field'};

				foreach ($custom_fields as $custom_field_node)
				{
					$field_value = CustomFields::getFieldValue($custom_field_node);
					$room->addProperty($field_value['field'], $field_value['value']);
				}

				$room->addProperty('creation_date', DateTime::createFromFormat(self::$DATETIME_FORMAT, $creation_date));
				$room->addProperty('last_update_date', DateTime::createFromFormat(self::$DATETIME_FORMAT, $last_update_date));

				$resultModel->addRoom($room);

				$resultModel->addFieldValue('new-flat', $new_flat);
				$resultModel->addFieldValue('object > id', $complex_id);
				$resultModel->addFieldValue('object > name', $complex_name);
				$resultModel->addFieldValue('object > location > country', $complex_location);
				$resultModel->addFieldValue('house > id', $house_id);
				$resultModel->addFieldValue('house > name', $house_name);
				$resultModel->addFieldValue('house > built-year', $house_built_year);
				$resultModel->addFieldValue('house > ready-quarter', $house_ready_quarter);
				$resultModel->addFieldValue('house > building-state', $house_building_state);
				$resultModel->addFieldValue('price > value', $price);
				$resultModel->addFieldValue('area > value', $area);
				$resultModel->addFieldValue('position-on-floor', $position_on_floor);
				$resultModel->addFieldValue('layout-code', $layout_code);
				$resultModel->addFieldValue('preset-code', $preset_code);
				$resultModel->addFieldValue('price-meter', $price_for_meter);
				$resultModel->addFieldValue('balcony-count', $balcony_count);
				$resultModel->addFieldValue('loggia-count', $loggia_count);
				$resultModel->addFieldValue('separated-bathroom-unit', $separated_bathroom_unit);
				$resultModel->addFieldValue('combined-bathroom-unit', $combined_bathroom_unit);
				$resultModel->addFieldValue('window-view', $window_view);
				$resultModel->addFieldValue('studio', $studio);
				$resultModel->addFieldValue('open-plan', $open_plan);
				$resultModel->addFieldValue('euro-layout', $euro_layout);
				$resultModel->addFieldValue('rooms', $rooms_count);
				$resultModel->addFieldValue('floor', $floor);
				$resultModel->addFieldValue('building-section', $section);
				$resultModel->addFieldValue('status_id', $status_id);
				$resultModel->addFieldValue('status', $status_alias);
				$resultModel->addFieldValue('status-humanized', $status_humanized);
				$resultModel->addFieldValue('facing', $facing);
				$resultModel->addFieldValue('description', $description);

				$skip_custom_fields = array(
					'main_ddu_date', 'main_ddu_number', 'main_ddu_reg_date'
				);

				foreach ($custom_fields as $custom_field_node)
				{
					$field_value = CustomFields::getFieldValue($custom_field_node);

					if (in_array($field_value['field'], $skip_custom_fields))
					{
						continue;
					}

					$resultModel->addFieldValue('custom-field > ' . $field_value['field'], $field_value['value']);
				}

				$this->reader->next('offer');
			}
		}

		return $resultModel;
	}

	private function openFileStream(string $xml_url): void
	{
		$this->reader = new XMLReader();
		$this->reader->open($xml_url);
	}
}