<?php

/**
 * Этот файл содержит WSettings class.
 *
 * @author WonderTalik <wondertalik@gmail.com>
 * @link http://it.bitmaster.dp.ua/
 * Date: 15.10.13
 * @copyright 2013
 */

namespace bitmaster\db\settings;

/**
 * Абстрактный класс для хранения параметров. Все параметры хранятся в массиве
 * в виде ключ->значение. Клас содержит методы для чтения и удаления этих параметров.
 * @package bitmaster\db\settings
 * @version 0.0.1
 */

abstract class WSettings {

    /**
     * @var array массив параметров ($key => $value)
     */
    protected $settings = array();

    /**
     * Добавляет в $settings елемент массива по ключу и значению.
     * @param string $key  ключ в массиве настроек
     * @param mixed $value значение в массиве настроек
     * @since 0.0.1
     */
    protected function setProperty($key, $value) {
        $this->settings[$key] = $value;
    }

    /**
     * Возвращает настройку из $settings по ключу.
     * @param $key ключ в массиве настроек
     * @param null @defaultValue значение по умолчанию
     * @return mixed|null
     * @since 0.0.1
     */
    protected function getProperty($key, $defaultValue = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $defaultValue;
    }

}