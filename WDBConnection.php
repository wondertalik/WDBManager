<?php
/**
 * Этот файл содержит WDBConnection class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * @copyright 2013
 */

namespace bitmaster\db;

require_once 'WDBCommand.php';
require_once 'db/IWDBList.php';
require_once 'schema/mysql/WMysqlSchema.php';
require_once 'settings/DBSettings.php';
require_once 'db/mysql/WDBMySQLConnectionManager.php';

use bitmaster\db\exception\WDBPDOException;
use bitmaster\db\schema\WDBSchema;
use bitmaster\db\settings\DBSettings;
use bitmaster\db\mysql\WDBMySQLConnectionManager;
use bitmaster\db\schema\mysql\WMysqlSchema;

use \PDO;

/**
 * WDBConnection содержит методы для установки соединения
 * @package bitmaster\db
 * @version 0.0.1
 */
class WDBConnection implements IWDBList {

    /**
     * @var DataBaseManager менеджер управления соединением
     */
    private $_databaseManager;

    /**
     * @var DBSettings объект параметров для подключения к бд.
     */
    private $_settings;

    /**
     * @var тип СУБД, все типы перечислены в виде констант в IWDBList
     */
    private $dbType;

    /**
     * @var bool флаг статуса соединения. true установлено, false соединение разорвано
     */
    private $_active = false;

    /**
     * @var WDBSchema схема выбранного типа бд
     */
    private $_schema;

    /**
     * Конструктор
     * Создает объект параметров для подключения к БД. Определяет тип БД.
     * @param string $user имя пользователя
     * @param string $passwd пароль пользователя
     * @param string $dbName имя базы данных
     * @param string $dbHost имя хоста для соединения
     * @param $dbType тип базы данных. Представляет собой одну из констант, перечислененных в IWDBList
     * @since 0.0.1
     */
    public function __construct($user = '', $passwd = '', $dbName = '', $dbHost = 'localhost', $dbType = WDBConnection::MYSQL) {
        $this->_settings = new DBSettings($user, $passwd, $dbName, $dbHost);
        $this->dbType = $dbType;
    }


    /**
     * Создает объект WDBCommand.
     * Автоматически производит соединение с бд, если оно было не установлено.
     * Возвращает объет WDBCommand {@link WDBCommand}
     * @param null|array|string $query параметр запроса, подробности синтаксиса {@link WDBCommand::__construct}
     * @return WDBCommand
     * @since 0.0.1
     */
    public function  createCommand($query = null) {
        $this->setActive(true);
        return new WDBCommand($this, $query);
    }

    /**
     * Получение ссылки на объект PDO, установленного соединения с бд
     * @return PDO
     * @since 0.0.1
     */
    public function getPDO() {
        return $this->_databaseManager->getPDO();
    }

    /**
     * Возвращает статус соединения
     * @return bool статус соединения
     * @since 0.0.1
     */
    public function getActive() {
        return $this->_active;
    }

    /**
     * Устанавливает/закрывает соединение с бд. В качестве параметра передается
     * булевое знаение true|false
     * @param bool $value флаг соединения
     * @throws WDBPDOException
     * @since 0.0.1
     */
    public function setActive($value) {
        if ( $value != $this->getActive()) {
            if ( $value )
                $this->open();
            else
                $this->close();
        }
    }

    /**
     * Устанавливает соединение с бд
     * @throws WDBPDOException
     * @since 0.0.1
     */
    protected  function open() {
        if ( $this->_databaseManager == null ) {
            $this->_databaseManager = $this->createPdoInstance();
            $this->_active = true;
        }
    }


    /**
     * Закрывает соединение с бд
     * @since 0.0.1
     */
    protected  function close() {
        $this->_active = false;
        $this->_databaseManager = null;
    }

    /**
     * Метод-фабрика. В зависимости от выбранного типа бд, создает требуемый менеджер бд.
     * @return DatabaseConnection менеджер соединения с бд
     * @throws WDBPDOException
     * @since 0.0.1
     */
    protected function createPdoInstance() {
        try {

            switch ($this->dbType) {
                case self::MYSQL:
                    $manager = new WDBMySQLConnectionManager();
                    break;
            }

            return $manager->getDatabase($this->_settings);

        } catch (WDBPDOException $e) {
            echo $e->getMessage();
        }

    }

    /**
     * Возвращает значение предопределенной константы установленной для данного соединения
     * @param $name integer константа специфического атрибута PDO
     * @return mixed
     * @see http://ua1.php.net/manual/ru/pdo.constants.php
     * @since 0.0.1
     */

    public function getAttribute($name) {
        $this->setActive(true);
        return $this->getPDO()->getAttribute($name);
    }


    /**
     * Устанавливает значение предопределенной константы установленной для данного соединения
     * @param int $name integer константа специфического атрибута PDO
     * @param mixed $value значение
     * @see http://ua1.php.net/manual/ru/pdo.setattribute.php
     * @since 0.0.1
     */
    public function setAttribute($name, $value) {
        $pdo = $this->getPDO();
        if ( $pdo instanceof PDO )
            $pdo->setAttribute($name, $value);
    }

    /**
     * @return WMysqlSchema|WDBSchema
     * @since 0.0.1
     */
    public function getSchema() {
        if ( $this->_schema !== null )
            return $this->_schema;
        else
            return new WMysqlSchema();
    }

    /**
     * Quotes a string value for use in a query.
     * @param string $str string to be quoted
     * @return string the properly quoted string
     * @see http://www.php.net/manual/en/function.PDO-quote.php
     * @since 0.0.1
     */
    public function quoteValue($str) {
        if ( is_int($str) || is_float($str) )
            return $str;

        $this->setActive(true);
        if ( ($value = $this->getPDO()->quote($str)) !== false )
            return $value;
        else // the driver doesn't support quote (e.g. oci)
        return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * @param string $name table name
     * @return string the properly quoted table name
     * @since 0.0.1
     */
    public function quoteTableName($name) {
        return $this->getSchema()->quoteTableName($name);
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * @param string $name column name
     * @return string the properly quoted column name
     * @since 0.0.1
     */
    public function quoteColumnName($name) {
        return $this->getSchema()->quoteColumnName($name);
    }

    /**
     * Determines the PDO type for the specified PHP type.
     * @param string $type The PHP type (obtained by gettype() call).
     * @return integer the corresponding PDO type
     * @since 0.0.1
     */
    public function getPdoType($type) {
        static $map = array
        (
            'boolean' => PDO::PARAM_BOOL,
            'integer' => PDO::PARAM_INT,
            'string' => PDO::PARAM_STR,
            'resource' => PDO::PARAM_LOB,
            'NULL' => PDO::PARAM_NULL,
        );
        return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
    }

    /**
     * Возвращает список доступных PDO драйверов
     * @return array список доступных PDO драйверов
     * @see http://ua1.php.net/manual/ru/pdo.getavailabledrivers.php
     * @since 0.0.1
     */
    public static function getAvailableDrivers() {
        return PDO::getAvailableDrivers();
    }

    /**
     * Возвращает ID последней вставленной строки или последовательное значение
     * @param string $sequenceName
     * @return string
     * @see http://ua1.php.net/manual/ru/pdo.lastinsertid.php
     * @since 0.0.1
     */
    public function getLastInsertID($sequenceName = '') {
        $this->setActive(true);
        return $this->getPDO()->lastInsertId($sequenceName);
    }

    /**
     * Returns the version information of the DB driver.
     * @return string the version information of the DB driver
     * @since 0.0.1
     */
    public function getClientVersion() {
        return $this->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }

    /**
     * Returns the status of the connection.
     * Some DBMS (such as sqlite) may not support this feature.
     * @return string the status of the connection
     * @since 0.0.1
     */
    public function getConnectionStatus() {
        return $this->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    }

    /**
     * Returns whether the connection performs data prefetching.
     * @return boolean whether the connection performs data prefetching
     * @since 0.0.1
     */
    public function getPrefetch() {
        return $this->getAttribute(PDO::ATTR_PREFETCH);
    }

    /**
     * Returns the information of DBMS server.
     * @return string the information of DBMS server
     * @since 0.0.1
     */
    public function getServerInfo() {
        return $this->getAttribute(PDO::ATTR_SERVER_INFO);
    }


    /**
     * Возвращает информацию о версии сервера баз данных, к которому подключен PDO.
     * @return string
     * @since 0.0.1
     */
    public function getServerVersion() {
        return $this->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Возвращает время в секундах, в течение которого должен быть завершен обмен с базой данных.
     * @return integer
     * @since 0.0.1
     */
    public function getTimeout() {
        return $this->getAttribute(PDO::ATTR_TIMEOUT);
    }


}
