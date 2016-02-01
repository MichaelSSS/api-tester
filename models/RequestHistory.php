<?php

namespace app\models;

class RequestHistory {
    const HISTORY_SIZE = 10;
    
    protected static $_inst;
    protected $_storage;
    protected $_data;
    protected $_saved;
    
    protected function __construct() {
        $this->_storage = \Yii::$app->basePath . '/data/request_history.txt';
        
        $sData = @file_get_contents($this->_storage);
        if ($sData) {
            $this->_data = unserialize($sData);
        } else {
            $this->_data = array();
        }
        
        $this->_saved = true;
    }
    
    public function __destruct() {
        if (!$this->_saved) {
            $this->save();
        }
    }
    
    public static function inst() {
        if (!isset(self::$_inst)) {
            self::$_inst = new self;
        }
        return self::$_inst;
    }
    
    public function add($method, $url, $params, $date) {
        $this->_data[] = [
            'date' => $date,
            'method' => $method,
            'url' => $url,
            'params' => $params
        ];
        while (count($this->_data) > self::HISTORY_SIZE) {
            array_shift($this->_data);
        }
        $this->_saved = false;
    }
    
    public function save() {
        file_put_contents($this->_storage, serialize($this->_data));
        $this->_saved = true;
    }
    
    public function getData() {
        return $this->_data;
    }
}