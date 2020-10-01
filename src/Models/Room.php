<?php
namespace M18\Profitbase\Models;

use DateTime;
use M18\Profitbase\Model;

final class Room extends Model
{
	public function isNewFlat(string $new_flat_value): bool
	{
		return !empty($new_flat_value);
	}

	public function isStudio(string $studio): bool
	{
		return !empty($studio);
	}

	public function getBookedDate(string $booked_date, string $format = 'd.m.Y')
	{
		if (!empty($booked_date))
		{
			$datetime = DateTime::createFromFormat($format, $booked_date);

			if (!$datetime)
			{
				return $booked_date;
			}

			return $datetime;
		}

		return '';
	}
}