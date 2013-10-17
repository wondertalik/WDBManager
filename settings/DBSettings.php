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
     * Константа ключа DSN
     */
    const DSN = 'dsn';

    /**
     * Добавляет в массив настроек данные для подключения к бд
     * @param $userName
     * @param $userPassword
     * @param $dbName
     * @param string $dbHost
     */

    function __construct($userName, $userPassword, $dbName, $dbHost = 'localhost') {
        $this->setDbHost($dbHost);
        $this->setUserName($userName);
        $this->setUserPassword($userPassword);
        $this->setDbName($dbName);
    }


    /**
     * Геттер имени пользователя
     * @return string|null
     * @since 0.0.1
     */
    public function getUserName() {
        return $this->getProperty(DBSettings::DBUSER);
    }

    /**
     * Сеттер имени пользователя
     * @param string $value
     */
    public function setUserName($value) {
        $this->setProperty(DBSettings::DBUSER, $value);
    }

    /**
     * Геттер пароля пользователя
     * @return string|null
     * @since 0.0.1
     */
    public function getUserPassword() {
        return $this->getProperty(DBSettings::DBPASS);
    }

    /**
     * Сеттер пароля пользователя
     * @param string $value
     */
    public function setUserPassword($value) {
        $this->setProperty(DBSettings::DBPASS, $value);
    }

    /**
     * Геттер имени БД
     * @return string|null
     * @since 0.0.1
     */
    public function getDbName() {
        return $this->getProperty(DBSettings::DBNAME);
    }

    /**
     * Сеттер имени БД
     * @param string $value
     * @since 0.0.1
     */
    public function setDbName($value) {
        $this->setProperty(DBSettings::DBNAME, $value);
    }

    /**
     * Геттер хоста подключения
     * @return string|null
     * @since 0.0.1
     */
    public function getDbHost() {
        return $this->getProperty(DBSettings::DBHOST);
    }

    /**
     * Сеттер хоста подключения
     * @param string $value
     */
    public function setDbHost($value) {
        $this->setProperty(DBSettings::DBHOST, $value);
    }

    /**
     * Геттер DSN
     * @return string|null
     * @since 0.0.1
     */
    public function getDsn() {
        return $this->getProperty(DBSettings::DSN);
    }

    /**
     * Сеттер DSN
     * @param string $value
     */
    public function setDsn($value) {
        $this->setProperty(DBSettings::DSN, $value);
    }

}
