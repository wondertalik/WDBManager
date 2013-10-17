<?php
 /**
 * Этот файл содержит WDBException class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 16.10.13
 * @copyright 2013
 */

namespace bitmaster\db\exception;

use \Exception;

/**
 * Исключение обертка для подключения к бд
 * @package bitmaster\db\exception
 * @version 0.0.1
 */
class WDBException extends Exception {

    /**
     * Конструктор
     * @param string $message подробности исключения
     */
    public function __construct($message) {
        $this->message = $message;
        parent::__construct($message, 0);
    }

    /**
     * Возвращает код ошибки в виде строки
     * @see http://ua1.php.net/manual/ru/pdostatement.errorinfo.php
     * @return string
     */
    public function __toString() {
        return $this->getMessage()."<br>";
    }

}