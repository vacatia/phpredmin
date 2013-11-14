<?php
final class App
{
    protected static $_instance = Null;

    protected $_data = Array();

    protected function __construct()
    {
        $this->_data['config']  = include_once(dirname(__FILE__) . '/../config.php');
        $this->_data['drivers'] = dirname(__FILE__) . '/drivers/';
    }

    public static function instance()
    {
        if (!self::$_instance)
            self::$_instance = new self;

        return self::$_instance;
    }

    public function __get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : Null;
    }
}
