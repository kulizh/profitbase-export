<?php
namespace M18\Profitbase;

use SimpleXMLElement;
use DateTime;
use M18\Profitbase\Models\Building;
use M18\Profitbase\Models\Complex;
use M18\Profitbase\Models\Room;
use M18\Profitbase\Models\Utils\CustomFields;
use M18\Profitbase\Models\SpecialOffer;
use M18\Profitbase\Models\Status;
use M18\Profitbase\Models\Result\Data as ResultData;

class XmlHandler extends Handlers\XML
{
	public function getData($resultModel = null, bool $get_values = true): ResultData
	{
		$resultModel = parent::getData($resultModel, $get_values);

		return $resultModel;
	}
}