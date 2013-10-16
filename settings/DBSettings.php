<?php
 /**
 * Этот файл содержит DBSettings class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */

namespace bitmaster\db\settings;

require_once 'WSettings.php';

/**
 * Класс настроек подключения к бд.
 * @package bitmaster\db\settings
 * @version 0.0.1
 */

class DBSettings extends WSettings {

    /**
     * Константа ключа настроек хоста
     */
    const DBHOST = 'dbhost';
    /**
     * Константа ключа настроек имени пользователя
     */
    const DBUSER = 'dbuser';
    /*
     * Константа ключа настроек пароля пользователя
     */
    const DBPASS = 'dbpass';
    /**
     * Константа ключа настроек имени базы данных
     */
    const DBNAME = 'dbname';

    /**
     * Добавляет в массив настроек данные для подключения к бд
     * @param $user
     * @param $passwd
     * @param $dbName
     * @param string $dbHost
     */

    function __construct($user, $passwd, $dbName, $dbHost = 'localhost') {
        $this->setProperty(DBSettings::DBHOST, $dbHost);
        $this->setProperty(DBSettings::DBUSER, $user);
        $this->setProperty(DBSettings::DBPASS, $passwd);
        $this->setProperty(DBSettings::DBNAME, $dbName);
    }

}
