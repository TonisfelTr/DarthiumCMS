<?php

namespace Engine\Services;

use Engine\LanguageAgent;
use Exceptions\TavernException;

class Datetime
{
    const MAKE_DECREASE = 1;
    const MAKE_INCREASE = 2;
    
    public int $year;
    public string $month;
    public int $yearWeek;
    public string $day;
    public string $hour;
    public string $minute;
    public string $second;
    public string $milliSeconds;
    
    public static function datetimeFromFormat($string) {
        //Format: Y-m-d H:i:s
        $month = [
            '01' => LanguageAgent::getTranslation("january_month"),
            '02' => LanguageAgent::getTranslation("febrary_month"),
            '03' => LanguageAgent::getTranslation("march_month"),
            '04' => LanguageAgent::getTranslation("april_month"),
            '05' => LanguageAgent::getTranslation("may_month"),
            '06' => LanguageAgent::getTranslation("june_month"),
            '07' => LanguageAgent::getTranslation("july_month"),
            '08' => LanguageAgent::getTranslation("august_month"),
            '09' => LanguageAgent::getTranslation("september_month"),
            '10' => LanguageAgent::getTranslation("october_month"),
            '11' => LanguageAgent::getTranslation("november_month"),
            '12' => LanguageAgent::getTranslation("december_month"),
        ];
        
        if ($string == "1970-01-01 00:00:00") {
            return LanguageAgent::getTranslation("never");
        }
        
        $parts  = explode(" ", $string);
        $date   = explode("-", $parts[0]);
        $result = $parts[1]
                  . " "
                  . $date[2]
                  . " "
                  . $month[$date[1]]
                  . " "
                  . $date[0]
                  . " "
                  . LanguageAgent::getTranslation("year");
        return $result;
    }
    
    public static function dateFromFormat(string $string) {
        $month = [
            '01' => LanguageAgent::getTranslation("january_month"),
            '02' => LanguageAgent::getTranslation("febrary_month"),
            '03' => LanguageAgent::getTranslation("march_month"),
            '04' => LanguageAgent::getTranslation("april_month"),
            '05' => LanguageAgent::getTranslation("may_month"),
            '06' => LanguageAgent::getTranslation("june_month"),
            '07' => LanguageAgent::getTranslation("july_month"),
            '08' => LanguageAgent::getTranslation("august_month"),
            '09' => LanguageAgent::getTranslation("september_month"),
            '10' => LanguageAgent::getTranslation("october_month"),
            '11' => LanguageAgent::getTranslation("november_month"),
            '12' => LanguageAgent::getTranslation("december_month"),
        ];
        
        $exploded = explode("-", $string);
        $result   = $exploded[2]
                    . " "
                    . $month[$exploded[1]]
                    . " "
                    . $exploded[0]
                    . " "
                    . LanguageAgent::getTranslation("year");
        return $result;
    }
    
    public static function fromTimeString(string $value): self {
        $obj = new self();
        
        $obj->setYear(date("Y", $value));
        $obj->setMonth(date("m", $value));
        $obj->setDay(date("d", $value));
        $obj->setWeek(date("W", $value));
        $obj->setHour(date("H", $value));
        $obj->setMinute(date("i", $value));
        $obj->setSecond(date("s", $value));
        $obj->setMilliSecond(date("v", $value));
        
        return $obj;
    }
    
    public function __construct(string $timeZone = null) {
        $timeZoneConverted = strtotime($timeZone . "hours" ?? time());
        
        $this->year     = date("Y", $timeZoneConverted);
        $this->month    = date("m", $timeZoneConverted);
        $this->yearWeek = date("W", $timeZoneConverted);
        $this->day      = date("d", $timeZoneConverted);
        $this->hour     = date("H", $timeZoneConverted);
        $this->minute   = date("i", $timeZoneConverted);
        $this->second   = date("s", $timeZoneConverted);
        $this->milliSeconds = date("v", $timeZoneConverted);
    }
    
    public function flushYear(): void {
        $this->year = date("Y");
    }
    
    public function flushMonth(): void {
        $this->month = date("m");
    }
    
    public function flushDay(): void {
        $this->day = date("d");
    }
    
    public function flushHour(): void {
        $this->hour = date("H");
    }
    
    public function flushMinute(): void {
        $this->date = date("i");
    }
    
    public function flushDate(): void {
        $this->date = date("s");
    }
    
    public function setYear(int $newValue): void {
        if (!checkdate($this->month, $this->day, $newValue)) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->year = $newValue;
    }
    
    public function setMonth(int $newValue): void {
        if (!checkdate($newValue, $this->day, $this->year)) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->month = $newValue;
    }
    
    public function setDay(int $newValue): void {
        if (!checkdate($this->month, $newValue, $this->year)) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->day = $newValue;
    }
    
    public function setWeek(int $newValue): void {
        if ($newValue < 1 || $newValue > (int)date("W", "{$this->year}-12-31")) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->yearWeek = $newValue;
    }
    
    public function setHour(int $newValue): void {
        if ($newValue > 24 || $newValue < 0) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->hour = $newValue;
    }
    
    public function setMinute(int $newValue): void {
        if ($newValue > 60 || $newValue < 0) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->minute = $newValue;
    }
    
    public function setSecond(int $newValue): void {
        if ($newValue > 60 || $newValue < 0) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->second = $newValue;
    }
    
    public function setMillisecond(int $newValue): void {
        if ($newValue > 20 || $newValue < 0) {
            // @todo: сделать эксепшен, если некорректная дата
            throw new TavernException();
        }
        
        $this->milliSeconds = $newValue;
    }
    
    public function increaseYears(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate + 1 year";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function increaseMonths(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula     = "$currentDate + 1 month";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function increaseDays(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula     = "$currentDate + 1 day";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function increaseWeeks(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula     = "$currentDate + 1 week";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function increaseHours(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula     = "$currentDate + 1 hour";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function increaseMinutes(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate + 1 minute";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function increaseSeconds(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate + 1 second";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function decreaseYears(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate - 1 year";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function decreaseMonths(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate - 1 month";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function decreaseDays(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate - 1 day";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function decreaseWeeks(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate - 1 week";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function decreaseHours(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate - 1 hour";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function decreaseMinutes(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate - 1 minute";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function decreaseSeconds(): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate - 1 second";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateYears(int $count): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate + ($count) years";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateMonth(int $count): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula     = "$currentDate + ($count) months";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateDays(int $count): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula     = "$currentDate + ($count) days";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateWeeks(int $count): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula     = "$currentDate + ($count) weeks";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateHours(int $count): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate + ($count) hours";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateMinutes(int $count): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate + ($count) minutes";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateSeconds(int $count): void {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $formula = "$currentDate + ($count) seconds";
        
        $this->year         = date("Y", strtotime($formula));
        $this->month        = date("m", strtotime($formula));
        $this->day          = date("d", strtotime($formula));
        $this->yearWeek     = date("W", strtotime($formula));
        $this->hour         = date("H", strtotime($formula));
        $this->minute       = date("i", strtotime($formula));
        $this->second       = date("s", strtotime($formula));
        $this->milliSeconds = date("v", strtotime($formula));
    }
    
    public function calculateWith(self $datetime, int $action = self::MAKE_INCREASE): self {
        $currentDate = "{$this->day}.{$this->month}.{$this->year} {$this->hour}:{$this->minute}:{$this->second}";
        $dtFormat    =
            "{$datetime->day}.{$datetime->month}.{$datetime->year} {$datetime->hour}:{$datetime->minute}:{$datetime->second}";
        
        if ($action == self::MAKE_INCREASE) {
            $this->year        = date("Y", strtotime($currentDate) + strtotime($dtFormat));
            $this->month       = date("m", strtotime($currentDate) + strtotime($dtFormat));
            $this->day         = date("d", strtotime($currentDate) + strtotime($dtFormat));
            $this->week        = date("W", strtotime($currentDate) + strtotime($dtFormat));
            $this->hour        = date("H", strtotime($currentDate) + strtotime($dtFormat));
            $this->minute      = date("i", strtotime($currentDate) + strtotime($dtFormat));
            $this->second      = date("s", strtotime($currentDate) + strtotime($dtFormat));
            $this->milliSecond = date("v", strtotime($currentDate) + strtotime($dtFormat));
        }
        else {
            $this->year        = date("Y", strtotime($currentDate) - strtotime($dtFormat));
            $this->month       = date("m", strtotime($currentDate) - strtotime($dtFormat));
            $this->day         = date("d", strtotime($currentDate) - strtotime($dtFormat));
            $this->week        = date("W", strtotime($currentDate) - strtotime($dtFormat));
            $this->hour        = date("H", strtotime($currentDate) - strtotime($dtFormat));
            $this->minute      = date("i", strtotime($currentDate) - strtotime($dtFormat));
            $this->second      = date("s", strtotime($currentDate) - strtotime($dtFormat));
            $this->milliSecond = date("v", strtotime($currentDate) - strtotime($dtFormat));
        }
    }
}