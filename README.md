[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/KRypt0nn/Qero/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/KRypt0nn/Qero/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/KRypt0nn/Qero/badges/build.png?b=master)](https://scrutinizer-ci.com/g/KRypt0nn/Qero/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/KRypt0nn/Qero/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![License](https://badges.frapsoft.com/os/gpl/gpl.png?v=103)](https://www.gnu.org/licenses/gpl-3.0.html)

# Qero
Qero - пакетный менеджер для PHP 7.4, представленный в виде распространяемого phar архива

## Сборка
Для сборки вам достаточно прописать команду в командной строке

```cmd
php build.php
```

находясь в основной директории проекта

> Если команда не запускается - пропишите полный путь до исполняемого PHP файла

После выполнения команды создастся файл ``qero.phar`` - главный и единственный файл проекта, а в консоль будет выведена различная информация о сборке

## Работа с Qero
Работа с Qero, как и с любыми другими phar архивами, может проходить как через командную консоль, так и через PHP код

Для просмотра списка команд вы можете вызвать

```cmd
php qero.phar help
```

**Qero** может работать с крупными проектами. К примеру, вы можете прямо "из коробки" запустить [**PHP-AI**](https://github.com/php-ai/php-ml) *(PHP 7.1+)*:

```cmd
php Qero.phar i php-ai/php-ml
```

```php
<?php

require 'qero-packages/autoload.php';

# А дальше код идёт прямо из примера на главной странице PHP-AI

use Phpml\Classification\KNearestNeighbors;

$samples = [[1, 3], [1, 4], [2, 4], [3, 1], [4, 1], [4, 2]];
$labels = ['a', 'a', 'a', 'b', 'b', 'b'];

$classifier = new KNearestNeighbors();
$classifier->train($samples, $labels);

echo $classifier->predict([3, 2]);
// return 'b'
```

## Создание Qero пакета

Для создания своего пакета вам нужно лишь создать репозиторий в GitHub и загрузить туда свой проект. Путь до вашего репозитория в адресной строке - и есть путь для установки через Qero

> Учтите, что если вы используете не GitHub, то вы так же должны указать источник пакета

Qero будет автоматически подключать файлы из главной директории репозитория со следующими названиями *(в порядке понижения приоритета)*:

* [название репозитория пакета].php
* index.php
* main.php

Если этого файла нет, то Qero сделает всё за вас. Однако учтите, что возможна некорректная работа пакета

Вы так же можете указать настройки для установки вашего пакета. Для этого создайте файл ``qero-package.json`` в корневой директории вашего репозитория. В этом файле вы можете прописать главную информацию для корректной работы Qero

### Доступные настройки:

Название | Описание
---------|---------
**version** | Версия пакета
**entry_point** | Точка входа пакета - PHP файл, который будет подключен автоматически
**requires** | Список зависимостей пакета. Они будут установлены вместе с пакетом и запущены до него
**after_install** | PHP файл, который будет подключен по окончанию загрузки пакета
**scripts** | Массив скриптов для Qero

К примеру:

```json
{
    "version": "1.0",
    "entry_point": "packet.php",
    "requires": [
        "KRypt0nn/ProgressBar",
        "KRypt0nn/ConsoleArgs"
    ],
    "after_install": "installed.php",
    "scripts": {
        "test": "echo Hello, World!"
    }
}
```

> Для примера вы можете посмотреть [этот](https://github.com/KRypt0nn/Qero-test-repo) репозиторий

Вот и всё. Приятного использования! :3

Автор: [Подвирный Никита](https://vk.com/technomindlp). Специально для [Enfesto Studio Group](https://vk.com/hphp_convertation)