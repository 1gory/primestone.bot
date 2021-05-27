<?php

class User
{
    private static $users = [];

    private static $instance;

    private static function getInstance(): User
    {
        if (!isset(self::$instance)) {
            self::$instance = new static;

            $usersData = explode("\n", file_get_contents(DATA_DIR . '/users.txt'));

            self::$users = array_filter($usersData);
        }

        return self::$instance;
    }

    public static function checkAdminAccess($userName)
    {
        if (!in_array($userName, ADMINS_USERNAME)) {
            die('Access denied!');
        }
    }

    public static function checkUserAccess($userName)
    {
        self::getInstance();

        foreach (self::$users as $user) {
            $user = explode(' ', $user);
            if ($user[0] === $userName) {
                return true;
            }
        }

        if (in_array($userName, ADMINS_USERNAME)) {
            return true;
        }

        die('Access denied!');
    }

    public static function getUserNameByLogin($login)
    {
        self::getInstance();
        foreach (self::$users as $user) {
            $userNameParts = explode(' ', $user);
            if ($userNameParts[0] === $login) {
                array_shift($userNameParts);
                return implode(' ', $userNameParts);
            }
        }

        return null;
    }

    public static function addUsersArray($users)
    {
        self::$users = $users;
        $usersData = implode("\n", self::$users);

        file_put_contents(DATA_DIR . '/users.txt', $usersData);
    }
}
