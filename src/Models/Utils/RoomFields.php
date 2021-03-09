<?php
namespace M18\Profitbase\Models\Utils;

class RoomFields
{
	private static $NODES = array(
		'is_new' => 'bool',
		'price' => 'float',
		'area' => 'float',
		'price-meter' => 'float',
		'balcony-count' => 'int',
		'loggia-count' => 'int',
		'separated-bathroom-unit' => 'bool',
		'combined-bathroom-unit' => 'bool',
		'window-view' => 'string',
		'studio' => 'bool',
		'rooms' => 'int',
		'floor' => 'int',
		'building-section' => 'string',
		'description' => 'string'
	);

	public static function set(array $nodes)
	{
		self::$NODES = array_merge(self::$NODES, $nodes);
	}

	public static function clear()
	{
		self::$NODES = array();
	}

	public static function get(): array
	{
		return self::$NODES;
	}
}