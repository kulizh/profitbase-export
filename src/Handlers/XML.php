<?php
namespace M18\Profitbase\Handlers;

use XMLReader;
use Exception;
use SimpleXMLElement;
use M18\Profitbase\Models\Result\Data as ResultData;

abstract class XML
{
	public static $URL = '';

	protected static $DATETIME_FORMAT = 'Y-m-d\TH:i:sP';

	protected $reader = null;

	abstract public function getData($resultModel = null): ResultData;

	protected function fetch($resultModel = null): ResultData
	{
		if (empty(self::$URL))
		{
			throw new Exception('No url to open');
		}

		$this->openFileStream(self::$URL);

		if (is_null($resultModel))
		{
			$resultModel = new ResultData();
		}

		// Переходим к узлу generation-date считываем дату генерации:
		$this->reader->read();
		$this->reader->read();

		$generation_date_node = new \SimpleXMLElement($this->reader->readOuterXml());
		$generation_date = $this->getValue($generation_date_node);

		$resultModel->setDatetime($generation_date, self::$DATETIME_FORMAT);

		// Переходим к нужному узлу offer
		while ($this->reader->read() && $this->reader->name !== 'offer') {}

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

	private function openFileStream(string $xml_url): void
	{
		$this->reader = new XMLReader();
		$this->reader->open($xml_url);
	}
}