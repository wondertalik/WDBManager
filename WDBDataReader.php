<?php
/**
 * Этот файл содержит WDBDataReader class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: ${DATE}
 * Time: ${TIME}
 * @copyright 2013
 */

namespace bitmaster\db;

use \PDO;

/**
 * WDBDataReader предоставляет методы для обработки данных, полученных
 * после выполнения SELECT запроса к бд.
 * @package bitmaster\db
 * @version 0.0.1
 */
class WDBDataReader {

    /**
     * @var PDOStatement ссылка на объект подготовленного запроса к БД
     */
    private $_statement;

    /**
     * @var mixed текущая считанная строка
     */
    private $_row;

    /**
     * Конструктор
     * @param WDBCommand $command
     * @since 0.0.1
     */
    public function __construct(WDBCommand $command) {
        $this->_statement = $command->getPdoStatement();
        $this->setFetchMode();
    }

    /**
     * Задает режим выборки по умолчанию для объекта запроса.
     * @param mixed $mode режим выборки
     * @see http://ua1.php.net/manual/ru/pdostatement.setfetchmode.php
     * @since 0.0.1
     */
    private function setFetchMode($mode = PDO::FETCH_ASSOC) {
        $this->_statement->setFetchMode($mode);
    }

    /**
     * Извлечение следующей строки из результирующего набора. Возвращает массив,
     * индексированный именами столбцов результирующего набора
     * @return array|false текущая строка, false если больше нет доступных строк
     * @see http://ua1.php.net/manual/ru/pdostatement.fetch.php
     * @since 0.0.1
     */
    public function readFetchAssoc() {
        $this->setFetchMode(PDO::FETCH_ASSOC);
        return $this->_row = $this->_statement->fetch();
    }

    /**
     * Извлечение следующей строки из результирующего набора. Возвращает массив,
     * индексированный номерами столбцов (начиная с 0)
     * @return array|false текущая строка, false если больше нет доступных строк
     * @see http://ua1.php.net/manual/ru/pdostatement.fetch.php
     * @since 0.0.1
     */
    public function readFetchNum() {
        $this->setFetchMode(PDO::FETCH_NUM);
        return $this->_row = $this->_statement->fetch();
    }

    /**
     * Извлечение следующей строки из результирующего набора. Возвращает массив,
     * индексированный именами столбцов результирующего набора, а также их номерами (начиная с 0)
     * @return array|false текущая строка, false если больше нет доступных строк
     * @see http://ua1.php.net/manual/ru/pdostatement.fetch.php
     * @since 0.0.1
     */
    public function read() {
        $this->setFetchMode(PDO::FETCH_BOTH);
        return $this->_row = $this->_statement->fetch();
    }

    /**
     * Возвращает данные одного столбца следующей строки результирующего набора.
     * @param integer $columnIndex zero-based column index
     * @return mixed|false значение поля текущей строки, false если больше нет доступных строк
     * @see http://ua1.php.net/manual/ru/pdostatement.fetchcolumn.php
     * @since 0.0.1
     */
    public function readColumn($columnIndex = 0) {
        return $this->_statement->fetchColumn($columnIndex);
    }

    /**
     * Возвращает количество строк, которые были затронуты в ходе выполнения последнего запроса
     * DELETE, INSERT или UPDATE
     * К сведению, для большинства СУБД PDOStatement::rowCount() не возвращает количество строк,
     * затронутых SELECT запросом. Вместо этого метода запустите через PDO::query() выражение
     * SELECT COUNT(*) с тем же текстом запроса. Затем методом PDOStatement::fetchColumn() вы
     * получите число строк в результирующем наборе. Эта методика будет работать со всеми СУБД.
     * @see http://ua1.php.net/manual/ru/pdostatement.rowcount.php
     * @since 0.0.1
     */
    public function getRowCount() {
        return $this->_statement->rowCount();
    }

    /**
     * Возвращает текущую считанную строку.
     * @return mixed текущая строка.
     * @since 0.0.1
     */
    public function current() {
        return $this->_row;
    }

    /**
     * Возвращает количество столбцов в результирующем наборе запроса PDOStatement.
     * Внимание, если нет результирующих строк, количество столбцов все равно вернет верное.
     * @return integer|0 integer количество столбцов в результирующем наборе запроса,
     * 0 если результирующего набора нет.
     * @see http://ua1.php.net/manual/ru/pdostatement.columncount.php
     * @since 0.0.1
     */
    public function getColumnCount() {
        return $this->_statement->columnCount();
    }






}