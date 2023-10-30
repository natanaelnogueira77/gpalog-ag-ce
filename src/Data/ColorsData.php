<?php 

namespace Src\Data;

use GTG\MVC\Router;
use Src\Models\User;

class ColorsData 
{
    private static function headerColors(): array 
    {
        return [
            'light' => 'bg-heavy-rain header-text-dark',
            'dark' => 'bg-slick-carbon header-text-light'
        ];
    }

    private static function leftColors(): array 
    {
        return [
            'light' => 'bg-heavy-rain sidebar-text-dark',
            'dark' => 'bg-slick-carbon sidebar-text-light'
        ];
    }

    public static function header(string $key): ?string 
    {
        return isset(self::headerColors()[$key]) ? self::headerColors()[$key] : null;
    }

    public static function left(string $key): ?string 
    {
        return isset(self::leftColors()[$key]) ? self::leftColors()[$key] : null;
    }
}