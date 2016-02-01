<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\helpers\MyCurl;
use yii\helpers\Json;

class UrlForm extends Model
{
    public $url;
    public $method = 'get';
    public $param_names;
    public $param_values;
    
    protected $_date;
    protected $_params;
    protected $_response;
    protected $_status;
    protected $_contentType;
    protected $_error;

    public function rules() {
        return [
            [['url','method'], 'required'],
            ['url', 'url', 'defaultScheme' => 'http'],
            [['param_names', 'param_values'], 'validateParams', 'params' => ['max_size' => 5]],
        ];
    }
    
    public function __get($name) {
        return isset($this->{'_' . $name}) ? $this->{'_' . $name} : null;
    }

    public function validateParams($attribute, $params) {
        if (!is_array($this->$attribute)) {
            return $this->addError($attribute, 'Corrupted parameters');
        }
        if (count($this->$attribute) > $params['max_size']) {
            return $this->addError($attribute, 'Parameters count exceeded limit (' . $params['max_size'] . ')');
        }
    }
    
    public function afterValidate() {
        parent::afterValidate();
        $this->url = str_replace(' ', '%20', $this->url);
        if ($this->_hasParams()) {
            $this->_combineParams();
        }
    }
    
    public function open($sizeLimit = false) {
        if ($this->validate()) {
            $curl = new MyCurl([ 'url' => $this->url ]);
            if ($this->method === 'post') {
                $curl->setPost($this->_params);
            } else {
                $curl->setGet($this->_params);
            }
            if ($sizeLimit) {
                $curl->addHeader('Range: bytes=0-' . (int) $sizeLimit);
            }
            
            $response = $curl->exec();
            
            $this->_date = date('Y-m-d H-i-s');
            $this->_status = $curl->getInfo('http_code');
            $this->_error = $curl->getError();
            $this->_contentType = $this->_parseContentType($curl->getInfo('content_type'));
            $this->_response = $this->_formatResponse($response);

            return true;
        }
        return false;
    }
    
    protected function _parseContentType($header) {
        if (strpos($header, 'application/json') === 0) return 'json';
        if (strpos($header, 'text/html') === 0) return 'html';
    }
    
    protected function _formatResponse($response) {
        if (!$response) {
            return '';
        }
        switch($this->_contentType) {
            case 'json': 
                return $this->_formatJson($response);
            case 'html':
                return $this->_formatHtml($response);
            default:
                return $this->_formatJson($response);
        }
    }
    
    protected function _formatJson($response) {
        try {
            $ar = Json::decode($response);
        } catch(yii\base\InvalidParamException $e) {
            return $this->_formatHtml($response);
        }
        return Json::encode(
            $ar,
            JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
                | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE
        );
    }
    
    protected function _formatHtml($response) {
        return htmlspecialchars($response);
    }
    
    protected function _hasParams() {
        return is_array($this->param_names);
    }

    protected function _combineParams() {
        foreach($this->param_names as $i => $name) {
            $value = $this->param_values[$i];
            if ($name !== '') {
                $this->_params[$name] = $value;
            }
        }
    }
//    
//    public function getParams() {
//        return $this->_params;
//    }
//    public function getStatus() {
//        return $this->_status;
//    }
//    public function getResponse() {
//        return $this->_response;
//    }
//    public function getError() {
//        return $this->_error;
//    }
}
