<?php
 /**
 * Этот файл содержит DatabaseManager class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */

namespace bitmaster\db;

use bitmaster\db\settings\DBSettings;
/**
 * Наследники этого класса обеспечивают управление соединением с конкретным типом бд
 * @package bitmaster\db
 * @version 0.0.1
 */
abstract class DatabaseManager {

    /**
     * @var DatabaseConnection экземляр соединения с бд
     * @since 0.0.1
     */
    private $databaseConnection;

    /**
     * Возвращает ссылку на объект класса PDO
     * @return PDO объэкт класса PDO
     * @since 0.0.1
     */
    public function getPDO() {
        return $this->getDatabaseConnection()->getPDO();
    }

    /**
     * Возвращает экземпляр соединения с бд
     * @return DatabaseConnection экземляр соединения с бд
     * @since 0.0.1
     */
    public function getDatabaseConnection() {
        return $this->databaseConnection;
    }

    /**
     * Инициализирует ссылку на объект соединения с бд
     * @param DatabaseConnection $conn объект соединения бд
     * @since 0.0.1
     */
    protected function  setDatabaseConnection(DatabaseConnection $conn) {
        $this->databaseConnection = $conn;
    }

    /**
     * Создание соединения с БД
     * @param DBSettings $settings параметры для подключения к бд
     * @param string $charset кодировка соединения
     * @return DatabaseConnection
     * @since 0.0.1
     */
    abstract function getDatabase(DBSettings $settings, $charset = 'utf8');
}
