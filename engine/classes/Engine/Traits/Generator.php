<?php

namespace Engine\Traits;

trait Generator
{
    public static function generateString(int $length = 8): string {
        $letters = [
            0  => 'a', 30 => 'A',
            1  => 'b', 31 => 'B',
            2  => 'c', 32 => 'C',
            3  => 'd', 33 => 'D',
            4  => 'e', 34 => 'E',
            5  => 'f', 35 => 'F',
            6  => 'g', 36 => 'G',
            7  => 'h', 37 => 'H',
            8  => 'j', 38 => 'J',
            9  => 'k', 39 => 'K',
            10 => 'l', 40 => 'L',
            11 => 'm', 41 => 'M',
            12 => 'n', 42 => 'N',
            13 => 'o', 43 => 'O',
            14 => 'p', 44 => 'P',
            15 => 'q', 45 => 'Q',
            16 => 'r', 46 => 'R',
            17 => 's', 47 => 'S',
            18 => 't', 48 => 'T',
            19 => 'u', 49 => 'U',
            20 => 'v', 50 => 'V',
            21 => 'w', 51 => 'W',
            22 => 'x', 52 => 'X',
            23 => 'y', 53 => 'Y',
            24 => 'z', 54 => 'Z',
            25 => '1', 55 => '2',
            26 => '3', 56 => '4',
            27 => '5', 57 => '6',
            28 => '7', 58 => '8',
            29 => '9', 59 => '0',
        ];
        
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $result .= $letters[rand(0, 59)];
        }
        
        return $result;
    }
    
    public static function generateHash(string $value): string {
        return hash("sha256", $value);
    }
}