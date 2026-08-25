<?php

namespace Engine;

use Engine\Services\ConsoleCommand;
use FilesystemIterator;

class ConsoleAgent
{
    private const COMMANDS_DIR = HOME_ROOT . "engine/customs/commands/";
    private static array $commands = [];
    private static array $commandsNames = [];
    
    private static function callCommand(string $commandName): void {
        self::$commands[$commandName]->runHandler();
    }
    
    protected static function echo(string $string): void {
        echo $string . PHP_EOL;
    }
    
    public static function registerCommand() : void {
        $fileIterator = new FilesystemIterator(self::COMMANDS_DIR, FilesystemIterator::SKIP_DOTS);
        
        if (iterator_count($fileIterator) == 0) {
            return;
        }
        
        foreach($fileIterator as $file) {
            /** @var ConsoleCommand $commandObj */
            $commandObj = include_once self::COMMANDS_DIR . $file->getFilename();
            if (in_array($commandObj->getName(), self::$commandsNames)) {
                return;
            }
            
            self::$commands[$commandObj->getName()] = $commandObj;
            self::$commandsNames[] = $commandObj->getName();
        }
    }
    
    public static function parseInserted(string $string): void {
        $consoleLine = preg_match_all();
        
        if (!isset(self::$commands[$consoleLine[0]])) {
            self::echo("Command \"$consoleLine[0]\" is not exists.");
        }
        
        if (self::$commands[$consoleLine[0]]->isArgumentExists($consoleLine[1])) {
            self::echo("Argument \"$consoleLine[1]\" is not exist in command \"$consoleLine[0]\".");
        }
    }
}