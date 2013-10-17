<?php
 /**
 * Этот файл содержит DatabaseConnection class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */


namespace bitmaster\db;

require_once __DIR__ . '/../exception/WDBPDOException.php';
require_once __DIR__ . '/../settings/DBSettings.php';

use \PDO;
use \PDOException;
use bitmaster\db\settings\DBSettings;
use bitmaster\db\exception\WDBPDOException;

/**
 * Абстрактрый класс, шаблон соединения с БД
 * Содержит методы для подключения к бд, при помощи PDO. Класс-наследник в обязательном порядке должен вызывать
 * конструктор родителя для установки с соединения с нужным типом бд.
 *
 * @package ua\dp\bitmaster\db
 * @version 0.0.1
 */
abstract class DatabaseConnection {

    /**
     * @var DBSettings типичные данные для подключения к бд
     */
    private $_settings;

    /**
     * @var PDO экземляр класса соединенения PDO
     * @link http://www.php.net/manual/ru/book.pdo.php net
     */
    private $_pdo;

    /**
     * Инициализирует настройки, устанавливает соединение
     * Базовый конструктор, инициализирует настройки, формирует строку dsn и устанавливает соединение
     * @param DBSettings $settings данные для подключения к бд {@link http://php.net}
     * @since 0.0.1
     */
    public function __construct(DBSettings $settings) {
        $this->_settings = $settings;
    }

    /**
     * Возвращает объект настроек
     * @return DBSettings данные для подключения к бд
     * @since 0.0.1
     */
    final public function getSettings() {
        return $this->_settings;
    }

    /**
     * Возвращает объект PDO
     * @return PDO экземпляр объэкта PDO
     * @see http://www.php.net/manual/ru/book.pdo.php Подробности на php.net
     * @since 0.0.1
     */
    final public function  getPDO() {
        return $this->_pdo;
    }

    /**
     * Устанавливает соединение с бд, в случае ошибок формирует исключение WDBPDOException
     * Указывает атрибуты по умолчанию для установленного соединения
     * @throws WDBPDOException
     * @since 0.0.1
     */
    public function createPDO() {
        $settings = $this->getSettings();
        try {
            $this->_pdo = new PDO($settings->getDsn(), $settings->getUserName(), $settings->getUserPassword());
            $this->setDefaultPDOAttributes();
        } catch (PDOException $e) {
            throw new WDBPDOException($e);
        }
    }

    /**
     * Включает выброс исключения PDOException {@link http://www.php.net/manual/ru/class.pdoexception.php}
     * Помимо задания кода ошибки PDO будет выбрасывать исключение PDOException,
     * свойства которого будут отражать код ошибки и ее описание
     * @see http://www.php.net/manual/ru/pdo.constants.php
     * @since 0.0.1
     */
    protected  function setDefaultPDOAttributes() {
        $this->getPDO()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Метод используется для инициализации параметров после установление соединения с бд.
     * Например,  необходимо явно сообщитьть MySQL в какой кодировке вы собираетесь
     * работать с базой данных с помощью 'set names ...'.
     * @return DatabaseConnection наследник абстрактного класса соединения бд DatabaseConnection
     * @since 0.0.1
     */
    abstract public  function init();

}