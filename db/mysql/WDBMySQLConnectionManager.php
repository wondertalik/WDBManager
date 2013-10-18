<?php
/**
 * Этот файл содержит WDBMySQLConnectionManager class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */

namespace bitmaster\db\mysql;

require_once __DIR__ . '/../DatabaseManager.php';
require_once __DIR__ . '/../../settings/DBSettings.php';
require_once 'WDBMySQLConnection.php';

use bitmaster\db\DatabaseManager;
use bitmaster\db\settings\DBSettings;
use bitmaster\db\mysql\WDBMySQLConnection;

/**
 * Менеджер бд MySql, содержит экземляр соединения с бд.
 * @package bitmaster\db
 * @version 0.0.1
 */
class WDBMySQLConnectionManager extends DatabaseManager {

    /**
     * Возвращает экземляр соединения с бд.
     * @param DBSettings $settings данные для подключения к бд {@link DBSettings}
     * @param string $charset кодировка соединения
     * @return WDBMySQLConnection
     */
    function getDatabase(DBSettings $settings, $charset = 'utf8') {
        $dataBaseConnection = new WDBMySQLConnection($settings, $charset);
        $this->setDatabaseConnection($dataBaseConnection);
        $dataBaseConnection->createPDO();
        return $this->getDatabaseConnection();
    }
}