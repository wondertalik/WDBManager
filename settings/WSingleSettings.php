<?php

/**
 * Этот файл содержит WSingleSettings class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */


namespace bitmaster\db\settings;

require_once 'WSettings.php';

/**
 * Класс настроек, обеспечивает создание единого экземляра класса. Реализия шаблона Singleton
 * @package bitmaster\db\settings
 * @version 0.0.1
 */
abstract class WSingleSettings extends WSettings {

    /**
     * @var объэкт класса CBSettings
     */
    private static $instance;

    /**
     * Закрытый конструктор, создание объекта производится статик методом
     * WSingleSettings::getInstance();
     */
    private function __construct() {
    }

    /**
     * Создает экземпляр наследованного класса от WSingleSettings
     * @return WSingleSettings
     * @since 0.0.1
     */
    public static function getInstance() {
        if ( empty(self::$instance) ) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}