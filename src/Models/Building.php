<?php
namespace M18\Profitbase\Models;

use M18\Profitbase\Model;

final class Building extends Model
{
	private $complex;

	public function addComplex(Complex $complex)
	{
		$this->complex = $complex;
	}

	public function getComplex(): Complex
	{
		return $this->complex;
	}
}