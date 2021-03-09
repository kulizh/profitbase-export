# Инструмент для экспорта данных из Profitbase
[![Build Status](https://travis-ci.com/kulizh/profitbase-export.svg?branch=master)](https://travis-ci.com/kulizh/profitbase-export)

Инструмент преобразовывает xml-выгрузку из profitbase в объект для последующей обработки.

## Установка
Последняя версия инструмента доступна для установки через Composer:
```
composer require m18/profitbase
```

## Генерация объекта
### Обработка фида
Для создания объекта нужно расширить абстрактный класс `M18\Profitbase\Handlers\XML`:
```php
<?php
namespace M18\Tools\Data\Profitbase;

use M18\Profitbase\Handlers\XML;
use M18\Profitbase\Models\Result\Data as ResultData;

final class Handler extends XML
{
	public function getData($resultModel = null, bool $get_values = true): ResultData
	{
		$resultModel = parent::getData($resultModel, $get_values);

		return $resultModel;
	}
}
```
При необходимости можно добавить дополнительную обработку объекта в этом классе. 
Для того, чтобы составить объект из нескольких фидов нужно передавать объект `$resultModel` в метод `Handler::getData()`. 

### Ссылка
Ссылка на XML-фид Profitbase имеет следуюший вид: `https://pbXXXX.profitbase.ru/export/profitbase_xml/{hash}`.
```php
Handler::$URL = 'https://pbXXXX.profitbase.ru/export/profitbase_xml/{hash}';
```

### Объединение нескольких фидов в один объект
```php
$handler = new Handler();
$url_list = $config->url_list;

$profitbase = null;

foreach ($url_list as $import_url)
{
	try {
		Handler::$URL = $import_url;
		$profitbase = $handler->getData($profitbase);
	}
	catch (Exception $exception)
	{
		die($exception->getMessage());
	}
}
```
### Поля для помещений
Для объекта помещения есть возможность указывать список получаемых полей. Например, если нужны не все узлы фида или использованы дополнительные элементы. 

За поля помещения отвечает класс \M18\Profitbase\Models\Utils\RoomFields. Объект Room будет иметь следующий набор полей по умолчанию:
* is_new
* price
* area
* price-meter
* balcony-count
* loggia-count
* separated-bathroom-unit
* combined-bathroom-unit
* window-view
* studio
* rooms
* floor
* building-section
* description

Для добавления дополнительного набора полей нужно использовать метод `\M18\Profitbase\Models\Utils\RoomFields::set(array $fieldset)`.
`$fieldset` представляет собой массив вида `array({название узла} => {тип поля})`

Для сброса текущего набора полей нужно использовать метод `\M18\Profitbase\Models\Utils\RoomFields::clear()`.

Пример:
```php
\M18\Profitbase\Models\Utils\RoomFields::set(array('kitchen-space' => 'float', 'living-space' => 'float'));
\M18\Profitbase\Models\Utils\RoomFields::set(array('floor' => 'string'));
```

### Кастомные поля 
В профитбейзе существуют так называемые Custom Fields. Названия этих узлов закодированы подобным образом: `pbcf_5b03c2b13a104`. Для того, чтобы повысить читаемость полей можно устанавливать алиасы для кастомных полей перед обработкой объекта.
```php
use M18\Profitbase\Models\Utils\CustomFields;

CustomFields::setAlias(array(
	'pbcf_5b03c2b13a104' => 'bti_number',
	'pbcf_5b03c2b141cc6' => 'bti_area',
	'pbcf_5b03c2b146165' => 'area_wo_balcony',
	'pbcf_5b03c2b149d47' => 'area_hallway',
));
 ```