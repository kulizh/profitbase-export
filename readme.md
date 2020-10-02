# Инструмент для экспорта данных из Profitbase
[![Build Status](https://travis-ci.com/kulizh/profitbase-export.svg?branch=master)](https://travis-ci.com/kulizh/profitbase-export)

## Подготовка к использованию
Для получения данных необходимо реализовать метод getData() абстрактного класса M18\Profitbase\Handlers\XML. Метод возвращает объект M18\Profitbase\Models\Result\Data. 
```php
use \M18\Profitbase\Models\Result\Data as DataModel;

public function getData(DataModel $data): DataModel
{
    $data = parent::getData($data);
    /*
    * Your code here...
    */
    return $data;
}
```
