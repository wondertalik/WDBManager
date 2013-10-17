<?php
/**
 * Этот файл содержит WDBMySQLConnection class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */

namespace bitmaster\db\mysql;

require_once __DIR__ . '/../DatabaseConnection.php';
require_once __DIR__ . '/../../exception/WDBPDOException.php';

use bitmaster\db\DatabaseConnection;
use bitmaster\db\settings\DBSettings;

/**
 * Реализует подключение к бд MySQL и первичную инициализацию
 * По умолчанию, кодировка соединения utf8
 * @package ua\dp\bitmaster\db
 * @version 0.0.1
 */
class WDBMySQLConnection extends DatabaseConnection {

    /**
     * @var string кодировка соединения
     */
    private $charset;

    /**
     * Инициализация соединения и установка кодировки соединения
     * В конструкторе инициализируется кодировка соединения и выполняется первичная инициализация.
     * В обязательном порядке требуется вызывать конструктор родителя, для фомрирования dsn, установки соединения
     * и получения экземляра класса PDO
     * @param DBSettings $settings типичные данные для подключения к бд {@link DBSettings}
     * @param string $charset кодировка соединения, по умолчанию ut f8
     * @since 0.0.1
     */
    public function __construct(DBSettings $settings, $charset = 'utf8') {
        parent::__construct($settings);
        $this->charset = $charset;
    }

    /**
     * Возвращает установленную кодировку соединения
     * @return string кодировка соединения
     * @since 0.0.1
     */
    public function getCharset() {
        return $this->charset;
    }

    /**
     * Устанавливает кодировку по умолчанию для соединения
     * @since 0.0.1
     */
    function init() {
        $pdo = $this->getPDO();
        $pdo->exec('SET NAMES ' . $pdo->quote($this->charset));
    }
}