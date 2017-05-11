<?php

namespace SFM\Console;

use Composer\Script\Event;
use SFM\Console\CreateEntity\MapperScaffold;
use SFM\Console\CreateEntity\EntityScaffold;
use SFM\Console\CreateEntity\AggregateScaffold;
use SFM\Console\CreateEntity\CriteriaScaffold;
use SFM\Console\CreateEntity\QueryBuilderScaffold;
use SFM\Console\CreateEntity\ScaffoldInterface;

class CreateEntityCommand
{
    protected static $path = "src/";

    public static function run(Event $event)
    {
        $tableValidator = function ($value) {
            if ('' === trim($value) || preg_match("/^\\w+(?:\\.\\w+)?$/", $value) === 0) {
                throw new \RuntimeException("Для создания сущности требуется имя базовой таблицы `table`");
            }
            return $value;
        };

        $classValidator = function ($value) {
            if ('' === trim($value) || preg_match("/^[a-zA-Z_][a-zA-Z_0-9]*$/", $value) === 0) {
                throw new \RuntimeException("Для создания сущности требуется имя класса");
            }
            return $value;
        };

        $table = $event->getIO()->askAndValidate("Для создания сущности требуется имя базовой таблицы `table`: ", $tableValidator);

        $class = str_replace(" ", "_", ucwords(str_replace("_", " ", $table)));
        $class = $event->getIO()->askAndValidate("Для создания сущности требуется имя класса: ", $classValidator, null, $class);

        $scaffolds = array(
            new MapperScaffold($table, $class),
            new EntityScaffold($table, $class),
            new AggregateScaffold($table, $class),
            new CriteriaScaffold($table, $class),
            new QueryBuilderScaffold($table, $class)
        );


        /** @var $scaffold ScaffoldInterface */
        foreach ($scaffolds as $scaffold) {
            $file = $scaffold->getFilename();
            $info = pathinfo($file);

            if (file_exists(self::$path . $file)) {
                $event->getIO()->write("<info>{$scaffold->getType()} `{$scaffold->getClass()}` already exists.</info>");
                continue;
            }

            if (false === is_dir(self::$path . $info['dirname'])) {
                mkdir(self::$path . $info['dirname'], 0775, true);
            }

            $result = file_put_contents(self::$path . $file, $scaffold->getScaffold());
            if (false === $result) {
                throw new \RuntimeException("Не удалось создать `{$file}`");
            }

            $event->getIO()->write("<info>{$scaffold->getType()} `{$scaffold->getClass()}` successfuly created.</info>");
        }
    }
}