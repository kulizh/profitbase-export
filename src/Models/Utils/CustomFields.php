<?php
namespace M18\Profitbase\Models\Utils;

use SimpleXMLElement;
use DateTime;

class CustomFields
{
	private static $ALIAS = array();

	public static function setAlias(array $alias)
	{
		self::$ALIAS = $alias;
	}

	public static function getFieldValue(SimpleXMLElement $node): array
	{
		$field_name = (string) $node->name;
		$field_id = (string) $node->field;
		$field_value = (string) $node->value;

		$alias = self::$ALIAS[$field_id] ?? $field_id;

		if (strrpos($alias, '_date') !== false)
		{
			$field_value = self::toDatetime($field_value);
		}

		return array(
			'field' => $alias,
			'value' => $field_value
		);
	}

	private static function toDatetime(string $str_datetime)
	{
		$format = 'd.m.Y';

		$formatted_date = DateTime::createFromFormat($format, $str_datetime);

		if (!$formatted_date)
		{
			return $str_datetime;
		}

		return $formatted_date;
	}
}