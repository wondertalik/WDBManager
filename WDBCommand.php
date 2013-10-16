<?php

/**
 * Этот файл содержит WDBCommand class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * @copyright 2013
 */


namespace bitmaster\db;

require_once "WDBDataReader.php";

use bitmaster\db\exception\WDBPDOException;
use bitmaster\db\WDBDataReader;
use \PDO;


/**
 * WDBCommand предоставляет методы для формирования и выполнения SQL выражений.
 *
 * Он обычно создается с помощью {@link WDBConnection::createCommand}.
 * SQL выражение, которое должно быть выполнено устанавливается с помощью {@link setText Text}.
 *
 * Для выполнения non-query SQL (таких как insert, delete, update), вызывать
 * {@link execute}. Для выполнения SQL-запросов, возвращающих наборы данных
 * (таких как SELECT), использовать {@link query}
 *
 * Если SQL-запрос (такой как SELECT SQL) возвращает результат, то для работы с данными
 * необходимо использовать методы {@link WDBDataReader}.
 *
 * WDBCommand поддерживает подготавливаемые запросы и привязку параметров.
 * Вызывать {@link bindParam} для привязки переменной PHP к параметру запроса SQL
 * Вызывать {@link bindValue) для привязки значения к параметру запроса SQL
 * CDbCommand supports SQL statement preparation and parameter binding.
 * Call {@link bindParam} to bind a PHP variable to a parameter in SQL.
 * Call {@link bindValue} to bind a value to an SQL parameter.
 * Во время привязки переменной, SQL выражение автоматический подготавливается.
 * Вы можете также вызвать {@link prepare} для явной подготовки SQL выражения
 *
 * WDBCommand также может быть использован как конструктор запросов, использовать
 * методы и свойства класса для того, чтобы указать отдельные части SQL-запроса
 *
 * Например,
 * <pre>
 * $st = db->createCommand()
 *     ->select('username, password')
 *     ->from('tbl_user')
 *     ->where('id=:id', array(':id'=>1))
 *     ->query();
 * </pre>
 *
 * @property string $_text SQL выражение, которое должно быть выполнено.
 * @property CDbConnection $connection соединение с бд, асоциированное с этой объектом.
 * @property PDOStatement $pdoStatement The underlying PDOStatement for this command
 * It could be null if the statement is not prepared yet.
 * @property string $select The SELECT part (without 'SELECT') in the query.
 * @property boolean $distinct A value indicating whether SELECT DISTINCT should be used.
 * @property string $from The FROM part (without 'FROM' ) in the query.
 * @property string $where The WHERE part (without 'WHERE' ) in the query.
 * @property mixed $join The join part in the query. This can be an array representing
 * multiple join fragments, or a string representing a single join fragment.
 * Each join fragment will contain the proper join operator (e.g. LEFT JOIN).
 * @property string $group The GROUP BY part (without 'GROUP BY' ) in the query.
 * @property string $having The HAVING part (without 'HAVING' ) in the query.
 * @property string $order The ORDER BY part (without 'ORDER BY' ) in the query.
 * @property string $limit The LIMIT part (without 'LIMIT' ) in the query.
 * @property string $offset The OFFSET part (without 'OFFSET' ) in the query.
 * @property mixed $union The UNION part (without 'UNION' ) in the query.
 * This can be either a string or an array representing multiple union parts.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @package bitmaster.db
 * @version 0.0.1
 */

class WDBCommand {

    /**
     * @var WDBConnection соединение с бд
     */
    private $_connection;
    /**
     * @var string текстовая строка запроса
     */
    private $_text;
    /**
     * @var array|string SQL выражение, которое требуется выполнить
     */
    private $_query;

    /**
     * @var \PDOStatement объект подготовленного запроса
     * @see http://ua1.php.net/manual/ru/class.pdostatement.php
     */
    private $_pdoStatement;

    /**
     * @var array массив значений входных параметров, если существуют псевдопеременные
     * @see http://ua1.php.net/manual/ru/pdostatement.execute.php
     */
    public $_params = array();

    /**
     * Конструктор
     * @param WDBConnection $connection соединение с базой данных
     * @param mixed|null $query запрос к базе данных для выполнения. Это может быть строка в виде
     * SQL запроса или массив чьи ключ=значение могут быть использованы для установки соответствующих
     * свойств созданного объекта команды.
     * Например, вы можете использовать один из двух вариантов <code>'SELECT * FROM tbl_user'</code>
     * или <code>array('select'=>'*', 'from'=>'tbl_user')</code>. Они эквиваленты с точки зрения
     * конечного результата запроса.
     *
     *  Когда передается запрос в виде массива, обычно устанавливаются следующие свойства:
     * {@link select}, {@link distinct}, {@link from}, {@link where}, {@link join},
     * {@link group}, {@link having}, {@link order}, {@link limit}, {@link offset} and
     * {@link union}. Пожалуйста, обратитесь к к документации каждого из этих методов для
     * получения дополнительных сведений об допустимости передаваемых аргументов.
     *
     * Есть возможность разные режимы выборки по умолчанию для объекта запроса {@link setFetchMode FetchMode}.
     * @see {@link http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php}
     */
    public function __construct(WDBConnection $connection, $query = null) {
        $this->_connection = $connection;

        if ( is_array($query) ) {
            foreach ($query as $name => $value)
                $this->_query[$name] = $value;
        } else {
            $this->setText($query);
        }
    }

    /**
     * Сбрасывает все свойства объекта WDBCommand к null
     * @return WDBCommand
     * @since 0.0.1
     */
    public function reset() {
        $this->_text = null;
        $this->_query = null;
        $this->_pdoStatement = null;
        $this->_params = array();

        return $this;
    }

    /**
     * Возвращает экземпляр соединения
     * @return WDBConnection соединение c БД
     * @since 0.0.1
     */
    public function getConnection() {
        return $this->_connection;
    }

    /**
     * Возвращает подготовленный запрос к базе данных
     * @return \PDOStatement подготовленный запрос.
     * Может быть равен null если подготовленный запрос еще не готов.
     * @since 0.0.1
     */
    public function getPdoStatement() {
        return $this->_pdoStatement;
    }

    /**
     * Возвращает сформированную строку SQL запроса
     * @return string строка SQL запроса для выполнения
     * @since 0.0.1
     */
    public function getText() {
        if ( $this->_text == '' && !empty($this->_query) )
            $this->setText($this->buildQuery($this->_query));
        return $this->_text;
    }

    /**
     * Устанавливает строку SQL запроса
     * @param string $value строка запроса
     * @return WDBCommand
     * @since 0.0.1
     */
    public function setText($value) {
        $this->_text = $value;
        $this->cancel();

        return $this;
    }

    /**
     * Очищает подготовленный запрос к базе данных
     * @since 0.0.1
     */
    public function cancel() {
        $this->_pdoStatement = null;
    }

    /**
     * Подготавливает SQL выражение для выполнения
     * @throws exception\WDBPDOException  выбрасывает исключение если подготовить запрос не удалось
     * @see http://ua1.php.net/manual/ru/pdo.prepare.php
     * @since 0.0.1
     */
    public function prepare() {
        if ( $this->getPdoStatement() == null ) {
            try {
                $this->_pdoStatement = $this->getConnection()->getPDO()->prepare($this->getText());
            } catch (\PDOException $e) {
                throw new WDBPDOException($e);
            }
        }
    }

    /**
     * Привязывает параметр SQL запроса к переменной
     *
     * Связывает PHP переменную с именованным или неименованным параметром подготавливаемого SQL запроса.
     * В отличие от PDOStatement::bindValue(), переменная привязывается по ссылке, и ее значение будет
     * вычисляться во время вызова PDOStatement::execute().
     * @param mixed $name идентификатор параметра. Для подготавливаемых запросов с именованными параметрами
     * это будет имя в виде :name. Если используются неименованные параметры (знаки вопроса ?)
     * это будет позиция псевдопеременной в запросе (начиная с 1).
     * @param mixed $value имя PHP переменной, которую требуется привязать к параметру SQL запроса.
     * @param integer $dataType явно заданный тип данных параметра. Тип задается одной из констант
     * PDO::PARAM_*. Если параметр используется в том числе для вывода информации из хранимой
     * процедуры, к значению аргумента data_type необходимо добавить PDO::PARAM_INPUT_OUTPUT,
     * используя оператор побитовое ИЛИ. Если равен null, тип данных определяется типом данных переменной.
     * @param integer $length Размер типа данных. Чтобы указать, что параметр используется для вывода
     * данных из хранимой процедуры, необходимо явно задать его размер.
     * @param mixed $driverOptions the driver-specific options (this is available since version 1.1.6)
     * @return WDBCommand
     * @see http://ua1.php.net/manual/ru/pdostatement.bindparam.php
     * @since 0.0.1
     */
    public function bindParam($name, &$value, $dataType = null, $length = null, $driverOptions = null) {
        $this->prepare();

        if ( $dataType === null )
            $this->getPdoStatement()->bindParam($name, $value, $this->_connection->getPdoType(gettype($value)));
        elseif ( $length === null )
            $this->getPdoStatement()->bindParam($name, $value, $dataType); elseif ( $driverOptions === null )
            $this->getPdoStatement()->bindParam($name, $value, $dataType, $length); else
            $this->getPdoStatement()->bindParam($name, $value, $dataType, $length, $driverOptions);

        return $this;
    }

    /**
     * Связывает параметр с заданным значением.
     *
     * @param mixed $name идентификатор параметра. Для подготавливаемых запросов с именованными параметрами
     * это будет имя в виде :name. Если используются неименованные параметры (знаки вопроса ?)
     * это будет позиция псевдопеременной в запросе (начиная с 1).
     * @param mixed $value имя PHP переменной, которую требуется привязать к параметру SQL запроса.
     * @param integer $dataType явно заданный тип данных параметра. Тип задается одной из констант
     * PDO::PARAM_*. Если параметр используется в том числе для вывода информации из хранимой
     * процедуры, к значению аргумента data_type необходимо добавить PDO::PARAM_INPUT_OUTPUT,
     * используя оператор побитовое ИЛИ. Если равен null, тип данных определяется типом данных переменной.
     *
     * @return WDBCommand
     * @see http://ua1.php.net/manual/ru/pdostatement.bindvalue.php
     * @since 0.0.1
     */
    public function bindValue($name, $value, $dataType = null) {
        $this->prepare();
        if ( $dataType === null )
            $this->getPdoStatement()->bindValue($name, $value, $this->_connection->getPdoType(gettype($value)));
        else
            $this->getPdoStatement()->bindValue($name, $value, $dataType);
//        $this->_paramLog[$name] = $value;
        return $this;
    }

    /**
     * Привязывает список значений соответствующих параметров
     * Аналогичен {@link bindValue} за исключением того, что привязывает несколько значений.
     * К сведению, тип данных SQL каждого значения определяется типом PHP
     * @param array $values значение, которые нужно привязать. Представляет собой асоциативный массив,
     * ключи выступают в роли имен псевдопеременных, а ключи их значениями.
     * Например, <code>array(':name'=>'John', ':age'=>25)</code>.
     * @return WDBCommand
     * @since 0.0.1
     */
    public function bindValues($values) {
        $this->prepare();
        foreach ($values as $name => $value) {
            $this->getPdoStatement()->bindValue($name, $value, $this->_connection->getPdoType(gettype($value)));
//            $this->_paramLog[$name] = $value;
        }
        return $this;
    }

    /**
     * Выполняет SQL-запросы, возвращающие наборы данных, например, запросы SELECT
     * @param array $params массив значений входных параметров (name=>value) для выполнения SQL запроса.
     * Это альтернативный вариант {@link bindParam} и {@link bindValue}.
     * Если у вас есть несколько входных параметров, передавая их таким образом может улучшить производительность.
     * Заметим, если вы передаете параметы таким способом,
     * вы не можете связывать параметр или значение с помощью {@link bindParam} или {@link bindValue} и наоборот.
     * Пожалуйста, обратите внимание, что все значения в этом случае обрабатываются как строки, если вам необходимо
     * обрабатывать с реальными типами данных, вы должны использовать {@link bindParam} или {@link bindValue}.
     * @return WDBDataReader результат выполнения запроса
     * @since 0.0.1
     */
    public function query($params = array()) {
        return $this->queryInternal($params);
    }

    /**
     * Выполняет SQL-запросы INSERT, UPDATE и DELETE. В случае успешного выполнения возвращает количество затронутых строк.
     * @param array $params массив значений входных параметров, если существуют псевдопеременные
     * @throws exception\WDBPDOException
     * @return int количество строк, модифицированных последним SQL запросом
     * @see http://ua1.php.net/manual/ru/pdostatement.execute.php
     * @since 0.0.1
     */
    public function execute($params = array()) {
        try {
            $this->prepare();
            if ( $params === array() )
                $this->getPdoStatement()->execute();
            else
                $this->getPdoStatement()->execute($params);

            $n = $this->getPdoStatement()->rowCount();
        } catch (\PDOException $e) {
            throw new WDBPDOException($e);
        }
        return $n;
    }

    /**
     * Выполняет SQL-запросы, возвращающие наборы данных, например, запросы SELECT
     * @param array $params массив значений входных параметров (name=>value) для выполнения SQL запроса.
     * Это альтернативный вариант {@link bindParam} и {@link bindValue}. Если у вас есть несколько входных параметров,
     * передавая их таким образом может улучшить производительность. Заметим, если вы передаете параметы таким способом,
     * вы не можете связывать параметр или значение с помощью {@link bindParam} или {@link bindValue} и наоборот.
     * Пожалуйста, обратите внимание, что все значения в этом случае обрабатываются как строки, если вам необходимо
     * обрабатывать с реальными типами данных, вы должны использовать {@link bindParam} или {@link bindValue}.
     * @throws WDBPDOException
     * @return WDBDataReader результат выполнения запроса
     * @since 0.0.1
     */
    private function queryInternal($params = array()) {
        $params = array_merge($this->_params, $params);

        try {

            $this->prepare();
            if ( $params === array() )
                $this->getPdoStatement()->execute();
            else
                $this->getPdoStatement()->execute($params);

            return new WDBDataReader($this);
        } catch (\PDOException $e) {
            throw new WDBPDOException($e);
        }
    }


    /**
     * Создает строку SQL выражения согласно спецификации запроса.
     * @param array $query the query specification in name-value pairs. Поддерживаются следующие
     * операторы запроса: {@link select}, {@link distinct}, {@link from},
     * {@link where}, {@link join}, {@link group}, {@link having}, {@link order},
     * {@link limit}, {@link offset} and {@link union}.
     * @throws CDbException if "from" key is not present in given query parameter
     * @return string SQL выраение
     * @since 0.0.1
     */
    private function buildQuery($query) {

        $sql = !empty($query['distinct']) ? 'SELECT DISTINCT' : 'SELECT';
        $sql .= ' ' . (!empty($query['select']) ? $query['select'] : '*');

        if ( !empty($query['from']) )
            $sql .= "\nFROM " . $query['from'];
//        else
//            throw new WDBPDOException(Yii::t('yii','The DB query must contain the "from" portion.'));

//        if(!empty($query['join']))
//            $sql.="\n".(is_array($query['join']) ? implode("\n",$query['join']) : $query['join']);

        if ( !empty($query['where']) )
            $sql .= "\nWHERE " . $query['where'];

        if ( !empty($query['group']) )
            $sql .= "\nGROUP BY " . $query['group'];

        if ( !empty($query['having']) )
            $sql .= "\nHAVING " . $query['having'];

        if ( !empty($query['union']) )
            $sql .= "\nUNION (\n" . (is_array($query['union']) ? implode("\n) UNION (\n", $query['union']) : $query['union']) . ')';

        if ( !empty($query['order']) )
            $sql .= "\nORDER BY " . $query['order'];

        $limit = isset($query['limit']) ? (int)$query['limit'] : -1;
        $offset = isset($query['offset']) ? (int)$query['offset'] : -1;

        if ( $limit >= 0 )
            $sql .= ' LIMIT ' . (int)$limit;

        if ( $offset > 0 )
            $sql .= ' OFFSET ' . (int)$offset;

        return $sql;
    }

    /**
     * Устанавливает часть SELECT запроса.
     * @param mixed $columns поля, которые требуется выбрать. По умолчанию '*', выборка все полей.
     * Поля могут быть указаны в виде строки (например, "id, name") или в виде массива (например, array('id', 'name')).
     * Поля могут содержать префикс таблицы (например, "tbl_name.id") и/или псевдонимы полей
     * (например, "tbl_name.id AS user_id"). Метод автоматически помещает имена полей в кавычки, если имена полей не
     * содержат скобки (это означает поле содержит DB выражение).
     *
     * @param string $option дополнительная опция, которая должна быть добавлена к "SELECT".
     * Например, В MySQL, может быть использована опция 'SQL_CALC_FOUND_ROWS'.
     * @return WDBCommand
     * @since 0.0.1
     */
    public function select($columns = '*', $option = '') {
        //
        if ( is_string($columns) && strpos($columns, '(') !== false ) {
            $this->_query['select'] = $columns;
        } else {

            if ( !is_array($columns) )
                $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($columns as $num => $column) {
                if ( is_object($column) )
                    $columns[$num] = (string)$column;
                //если нет скобок, автоматически помещаем в кавычки
                elseif ( strpos($column, '(') === false ) {
                    if ( preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $column, $matches) )
                        $columns[$num] = $this->_connection->quoteColumnName($matches[1]) . ' AS ' . $this->_connection->quoteColumnName($matches[2]);
                    else
                        $columns[$num] = $this->_connection->quoteColumnName($column);
                }
            }
        }
        $this->_query['select'] = implode(', ', $columns);
//        echo "<pre>";
//        print_r($this->_query);
//        echo "</pre>";
        if ( $option != '' )
            $this->_query['select'] = $option . ' ' . $this->_query['select'];

        return $this;
    }

    /**
     * Возвращает SELECT часть запроса
     * @return string SELECT часть запроса (без SELECT)
     * @since 0.0.1
     */
    public function getSelect() {
        return isset($this->_query['select']) ? $this->_query['select'] : '';
    }

    /**
     * Sets the SELECT part of the query with the DISTINCT flag turned on.
     * This is the same as {@link select} except that the DISTINCT flag is turned on.
     * @param mixed $columns the columns to be selected. See {@link select} for more details.
     * @return CDbCommand the command object itself
     * @since 0.0.1
     */
    public function selectDistinct($columns = '*') {
        $this->_query['distinct'] = true;
        return $this->select($columns);
    }

    /**
     * Возвращает флаг-значение, указывающее на использование DISTINCT.
     * @return boolean a value флаг значение, указывающее на использование DISTINCT.
     * @since 0.0.1
     */
    public function getDistinct() {
        return isset($this->_query['distinct']) ? $this->_query['distinct'] : false;
    }

    /**
     * Возвращает часть FROM запроса
     * @param $tables таблица(ы) из проводится выборка. Эта может быть строка (например, 'tbl_user')
     * или массив (например, array('tbl_user', 'tbl_profile')) с одной или несколькими указаными именами таблиц.
     * Имя таблицы может содержать префикс (имя бд или схемы) и/или псевдоним таблицы
     * Метод автоматически помещает имена полей в кавычки, если имена полей не
     * содержат скобки (это означает поле содержит DB выражение).
     * @return WDBCommand
     * @since 0.0.1
     */
    public function from($tables) {
        if ( is_string($tables) && strpos($tables, '(') !== false )
            $this->_query['from'] = $tables;
        else {
            if ( !is_array($tables) )
                $tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tables as $i => $table) {
                if ( strpos($table, '(') === false ) {
                    if ( preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $table, $matches) ) // with alias
                    $tables[$i] = $this->_connection->quoteTableName($matches[1]) . ' ' . $this->_connection->quoteTableName($matches[2]);
                    else
                        $tables[$i] = $this->_connection->quoteTableName($table);
                }
            }
            $this->_query['from'] = implode(', ', $tables);
        }
        return $this;
    }

    /**
     * Возвращает часть FROM запроса
     * @return string FROM часть запроса (без FROM).
     * @since 0.0.1
     */
    public function getFrom() {
        return isset($this->_query['from']) ? $this->_query['from'] : '';
    }

    /**
     * Устанавливает WHERE часть запроса
     *
     * В метод передается параметр $conditions и опционально массив $params,
     * определяющий значения, который должны быть привязаны к параметрам запроса
     *
     * Аргумент $conditions может быть строкой (например, 'id=1') или массив.
     * Если массив, он должен быть формата <code>array(operator, operand1, operand2, ...)</code>,
     * где оператор может быть одним из следующих, операнды зависят от оператора:
     *
     * <ul>
     * <li><code>and</code>: операнды объединяется с использованием оператора AND. Например,
     * array('and', 'id=1', 'id=2') преобразуется строку 'id=1 AND id=2'. Если операнд массив,
     * он будет преобразован в массив с использованием правил, описанных ниже. Например,
     * array('and', 'type=1', array('or', 'id=1', 'id=2'))  преобразуется в 'type=1 AND (id=1 OR id=2)'.
     * Метод не экранирует и не помещает в кавычки операнды.</li>
     * <li><code>or</code>: аналогичен <code>and</code> за исключение того, что операнды объединены оператором OR.</li>
     * <li><code>in</code>: operand 1 должен быть полем или выражением БД, and operand 2 массив, представляющий
     * диапазон значений поля. Например, array('in', 'id', array(1,2,3)) преобразуется в 'id IN (1,2,3)'.
     * Метод заключает в кавычки имена полей и экранирует диапазон значений.</li>
     * <li><code>not in</code>: аналогичен <code>in</code> за исключением того, того что заменяется на NOT IN
     * в генерируемом исключении.</li>
     * <li><code>like</code>: operand 1 должен быть полем или выражением бд, and operand 2 строкой или массивом
     * представляющие значения, которым соответствуют значения поля или выражения бд
     * Например, array('like', 'name', '%tester%') преобразовуется "name LIKE '%tester%'".
     * Когда диапазон значенией передается в виде массиве, несколько предикатов LIKE будут
     * сгенерированы и объединены при помощи AND. Например, array('like', 'name', array('%test%', '%sample%'))
     * генерирует "name LIKE '%test%' AND name LIKE '%sample%'".
     * Метод заключает в кавычки имена полей и экранируен диапазон значений.</li>
     * <li><code>not like</code>: аналогичен <code>like</code> за исключенем того, что заменяется на NOT LIKE
     * в генерируемом исключении.</li>
     * <li><code>or like</code>: аналогичен <code>like</code> за исключением того, что предикаты LIKE объединяются
     * оператором OR</li>
     * <li><code>or not like</code>: аналогичен <code>not like</code> за исключением того, что предикаты NOT LIKE
     * объединяются при помощи оператора OR</li>
     * </ul>
     * @param mixed $conditions условия, которые должны быть в части WHERE запроса.
     * @param array $params the parameters (name=>value) которые должны быть связаны с запросом
     * @return WDBCommand
     * @since 0.0.1
     */
    public function where($conditions, $params = array()) {

        $this->_query['where'] = $this->processConditions($conditions);

        foreach ($params as $name => $value)
            $this->_params[$name] = $value;

        return $this;
    }

    /**
     * Добавляет новые условия к существующим объединяя оператором 'AND'
     *
     * Этот метод работает почти так же как и (@link where) за исключение того, что добавляет условия
     * с оператором 'AND', но не заменяет их новыми. Подробности в документаци {@link where}
     *
     * @param mixed $conditions условия которые должны быть добавлены WHERE part.
     * @param array $params the parameters (name=>value) to be bound to the query.
     * @return WDBCommand the command object itself.
     * @since 0.0.1
     */
    public function andWhere($conditions, $params = array()) {
        if ( isset($this->_query['where']) )
            $this->_query['where'] = $this->processConditions(array('AND', $this->_query['where'], $conditions));
        else
            $this->_query['where'] = $this->processConditions($conditions);

        foreach ($params as $name => $value)
            $this->_params[$name] = $value;
        return $this;
    }

    /**
     * Добавляет новые условия к существующим объединяя оператором 'OR'
     *
     * Этот метод работает почти так же как и (@link where) за исключение того, что добавляет условия
     * с оператором 'OR', но не заменяет их новыми. Подробности в документаци {@link where}
     *
     * @param mixed $conditions условия которые должны быть добавлены WHERE part.
     * @param array $params набор значений (name=>value) которые должны быть привязаны к параметрам в запросе.
     * @return WDBCommand the command object itself.
     * @since 0.0.1
     */

    public function orWhere($conditions, $params = array()) {
        if ( isset($this->_query['where']) )
            $this->_query['where'] = $this->processConditions(array('OR', $this->_query['where'], $conditions));
        else
            $this->_query['where'] = $this->processConditions($conditions);

        foreach ($params as $name => $value)
            $this->_params[$name] = $value;
        return $this;
    }

    /**
     * Возвращает часть запроса WHERE
     * @return string часть запроса WHERE (без 'WHERE' ).
     * @since 0.0.1
     */
    public function getWhere() {
        return isset($this->_query['where']) ? $this->_query['where'] : '';
    }

    /**
     * Устанавливает часть запроса GROUP BY.
     * @param mixed $columns поля, по которым необходимо группировать.
     * Поля могут быть перечислены в виде строки (например, 'id, name') или в виде массива
     * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
     * The method will automatically quote the column names unless a column contains some parenthesis
     * (which means the column contains a DB expression).
     * @return CDbCommand the command object itself
     * @since 0.0.1
     */
    public function group($columns) {
        if ( is_string($columns) && strpos($columns, '(') !== false )
            $this->_query['group'] = $columns;
        else {
            if ( !is_array($columns) )
                $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($columns as $i => $column) {
                if ( is_object($column) )
                    $columns[$i] = (string)$column;
                elseif ( strpos($column, '(') === false )
                    $columns[$i] = $this->_connection->quoteColumnName($column);
            }
            $this->_query['group'] = implode(', ', $columns);
        }
        return $this;
    }

    /**
     * Возвращает часть запроса GROUP BY
     * @return string часть запроса GROUP BY (без 'GROUP BY' ).
     * @since 0.0.1
     */
    public function getGroup() {
        return isset($this->_query['group']) ? $this->_query['group'] : '';
    }

    /**
     * Sets the HAVING part of the query.
     * @param mixed $conditions the conditions to be put after HAVING.
     * Please refer to {@link where} on how to specify conditions.
     * @param array $params the parameters (name=>value) to be bound to the query
     * @return CDbCommand the command object itself
     * @since 0.0.1
     */
    public function having($conditions, $params = array()) {
        $this->_query['having'] = $this->processConditions($conditions);
        foreach ($params as $name => $value)
            $this->params[$name] = $value;
        return $this;
    }

    /**
     * Returns the HAVING part in the query.
     * @return string the HAVING part (without 'HAVING' ) in the query.
     * @since 1.1.6
     */
    public function getHaving() {
        return isset($this->_query['having']) ? $this->_query['having'] : '';
    }

    /**
     * Устанавливает ORDER BY xfcnm запроса.
     * @param mixed $columns поля (и тип сортировки).
     * Поля могут быть в виде строки (например, "id ASC, name DESC") или массива (например, array('id ASC', 'name DESC')).
     * Метод автоматически помещает имена полей в кавычки, если имена полей не
     * содержат скобки (это означает поле содержит DB выражение).
     *
     * Например, для получения "ORDER BY 1" необходимо
     *
     * <pre>
     * $criteria->order('(1)');
     * </pre>
     *
     * @return WDBCommand
     * @since 0.0.1
     */
    public function order($columns) {
        if ( is_string($columns) && strpos($columns, '(') !== false )
            $this->_query['order'] = $columns;
        else {
            if ( !is_array($columns) )
                $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($columns as $i => $column) {
                if ( is_object($column) )
                    $columns[$i] = (string)$column;
                elseif ( strpos($column, '(') === false ) {
                    if ( preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches) )
                        $columns[$i] = $this->_connection->quoteColumnName($matches[1]) . ' ' . strtoupper($matches[2]);
                    else
                        $columns[$i] = $this->_connection->quoteColumnName($column);
                }
            }
            $this->_query['order'] = implode(', ', $columns);
        }
        return $this;
    }

    /**
     * Возвращает часть запроса ORDER BY.
     * @return string ORDER BY часть (без 'ORDER BY' ).
     * @since 0.0.1
     */
    public function getOrder() {
        return isset($this->_query['order']) ? $this->_query['order'] : '';
    }

    /**
     * Устанавливает чаcть LIMIT запроса.
     * @param integer $limit the limit
     * @param integer $offset смещение
     * @return WDBCommand
     * @since 0.0.1
     */
    public function limit($limit, $offset = null) {
        $this->_query['limit'] = (int)$limit;
        if ( $offset !== null )
            $this->offset($offset);
        return $this;
    }

    /**
     * Возвращает часть LIMIT.
     * @return string the LIMIT part (without 'LIMIT' ) in the query.
     * @since 0.0.1
     */
    public function getLimit() {
        return isset($this->_query['limit']) ? $this->_query['limit'] : -1;
    }

    /**
     * Sets the OFFSET part of the query.
     * @param integer $offset the offset
     * @return CDbCommand the command object itself
     * @since 0.0.1
     */
    public function offset($offset) {
        $this->_query['offset'] = (int)$offset;
        return $this;
    }

    /**
     * Returns the OFFSET part in the query.
     * @return string the OFFSET part (without 'OFFSET' ) in the query.
     * @since 0.0.1
     */
    public function getOffset() {
        return isset($this->_query['offset']) ? $this->_query['offset'] : -1;
    }

    /**
     * Добавляет SQL выражение используя UNION.
     * @param string $sql the SQL выражение, которое объединяется оператором UNION
     * @return WDBCommand
     * @since 0.0.1
     */
    public function union($sql) {
        if ( isset($this->_query['union']) && is_string($this->_query['union']) )
            $this->_query['union'] = array($this->_query['union']);

        $this->_query['union'][] = $sql;

        return $this;
    }

    /**
     * Возвращает часть запроса UNION.
     * @return mixed the UNION часть запроса (без 'UNION' ).
     * Это может быть строка или массив содержащий несколько частей UNION
     * @since 0.0.1
     */
    public function getUnion() {
        return isset($this->_query['union']) ? $this->_query['union'] : '';
    }

    /**
     * Генерирует условия, которые долны быть в WHERE части запроса
     * @param $conditions условия запроса после оператора WHERE
     * @param array $params
     * @return string сформированная строка условий
     * @since 0.0.1
     */
    public function processConditions($conditions, $params = array()) {

        if ( !is_array($conditions) )
            return $conditions;
        elseif ( $conditions === array() )
            return '';
        $n = count($conditions);
        $operator = strtoupper($conditions[0]);
        if ( $operator === 'OR' || $operator === 'AND' ) {
            $parts = array();
            for ($i = 1; $i < $n; ++$i) {
                $condition = $this->processConditions($conditions[$i]);
                if ( $condition !== '' )
                    $parts[] = '(' . $condition . ')';
            }
            return $parts === array() ? '' : implode(' ' . $operator . ' ', $parts);
        }

        if ( !isset($conditions[1], $conditions[2]) )
            return '';

        $column = $conditions[1];
        if ( strpos($column, '(') === false )
            $column = $this->_connection->quoteColumnName($column);

        $values = $conditions[2];
        if ( !is_array($values) )
            $values = array($values);

        if ( $operator === 'IN' || $operator === 'NOT IN' ) {
            if ( $values === array() )
                return $operator === 'IN' ? '0=1' : '';
            foreach ($values as $i => $value) {
                if ( is_string($value) )
                    $values[$i] = $this->_connection->quoteValue($value);
                else
                    $values[$i] = (string)$value;
            }
            return $column . ' ' . $operator . ' (' . implode(', ', $values) . ')';
        }

        if ( $operator === 'LIKE' || $operator === 'NOT LIKE' || $operator === 'OR LIKE' || $operator === 'OR NOT LIKE' ) {
            if ( $values === array() )
                return $operator === 'LIKE' || $operator === 'OR LIKE' ? '0=1' : '';

            if ( $operator === 'LIKE' || $operator === 'NOT LIKE' )
                $andor = ' AND ';
            else {
                $andor = ' OR ';
                $operator = $operator === 'OR LIKE' ? 'LIKE' : 'NOT LIKE';
            }
            $expressions = array();
            foreach ($values as $value)
                $expressions[] = $column . ' ' . $operator . ' ' . $this->_connection->quoteValue($value);
            return implode($andor, $expressions);
        }
    }

}