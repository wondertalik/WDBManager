<?php
/**
 * Этот файл содержит WDBSchema class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */

namespace bitmaster\db\schema;

/**
 * Class WDBSchema
 * @package bitmaster\db\schema
 * @version 0.0.1
 */
abstract class WDBSchema {

    /**
     * Помещает имя таблицы в кавычки.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * @param string $name имя таблицы
     * @return string имя таблицы помещенное в кавычки
     * @since 0.0.1
     * @see quoteSimpleTableName
     */
    public function quoteTableName($name) {
        if ( strpos($name, '.') === false )
            return $this->quoteSimpleTableName($name);
        $parts = explode('.', $name);
        foreach ($parts as $i => $part)
            $parts[$i] = $this->quoteSimpleTableName($part);
        return implode('.', $parts);

    }

    /**
     * Quotes a simple table name for use in a query.
     * A simple table name does not schema prefix.
     * @param string $name table name
     * @return string the properly quoted table name
     * @since 0.0.1
     */
    public function quoteSimpleTableName($name) {
        return "`" . $name . "`";
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * @param string $name column name
     * @return string the properly quoted column name
     * @see quoteSimpleColumnName
     */
    public function quoteColumnName($name) {
        if ( ($pos = strrpos($name, '.')) !== false ) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else
            $prefix = '';
        return $prefix . ($name === '*' ? $name : $this->quoteSimpleColumnName($name));
    }

    /**
     * Quotes a simple column name for use in a query.
     * A simple column name does not contain prefix.
     * @param string $name column name
     * @return string the properly quoted column name
     * @since 1.1.6
     */
    public function quoteSimpleColumnName($name) {
        return '`' . $name . '`';
    }

}