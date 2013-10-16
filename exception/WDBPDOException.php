<?php

/**
 * Этот файл содержит WDBPDOException class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */

namespace bitmaster\db\exception;

use \Exception;
use \PDOException;

/**
 * Исключение обертка для подключения к бд
 * @package bitmaster\db\exception
 * @version 0.0.1
 */
class WDBPDOException extends \Exception {

    /**
     * Конструктор
     * @param PDOException $previous
     */
    public function __construct(PDOException $previous) {
        $this->message = basename($previous->file).", line ".$previous->line.": <br>\n".$previous->getMessage();
        parent::__construct($this->getMessage(), 0, $previous);
    }

    /**
     * Возвращает код ошибки в виде строки
     * @see http://ua1.php.net/manual/ru/pdostatement.errorinfo.php
     * @return string
     */
    public function __toString() {
        return $this->getMessage()."<br>";
    }


    /**
     * Возвращает трассировку предыдующего исключения
     * @return array
     */
    public function getPreviousTrace() {
        return $this->getPrevious()->getTrace();
    }

}