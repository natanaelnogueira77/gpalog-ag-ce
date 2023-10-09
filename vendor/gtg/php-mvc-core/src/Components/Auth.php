<?php

namespace GTG\MVC\Components;

use GTG\MVC\Application;
use GTG\MVC\DB\UserModel;

class Auth 
{
    public static string $userKey = '';

    public function __construct(string $userKey)
    {
        self::$userKey = $userKey;
    }

    public function get(): ?UserModel 
    {
        return Application::$app->session->get(self::$userKey);
    }

    public function set(UserModel $user): void 
    {
        Application::$app->session->set(self::$userKey, $user);
    }

    public function destroy(): void 
    {
        Application::$app->session->remove(self::$userKey);
    }
}