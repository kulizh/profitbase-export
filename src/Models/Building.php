<?php
namespace M18\Profitbase\Models;

final class Building extends AbstractModel
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