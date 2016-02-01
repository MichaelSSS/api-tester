<?php

namespace app\helpers;

class MyCurl {
    protected $_url;
    protected $_followlocation;
    protected $_timeout;
    protected $_maxRedirects;
    protected $_headers;
    protected $_post;
    protected $_postFields;
    protected $_response;
    protected $_status;
    protected $_info;
    protected $_error;
    

    public function __construct($opts) {
        $this->_url = $opts['url'];
        $this->_followlocation = isset($opts['followLocation']) ? $opts['followLocation'] : false;
        $this->_timeout = isset($opts['timeOut']) ? $opts['timeOut'] : 4;
        $this->_maxRedirects = isset($opts['maxRedirects']) ? $opts['maxRedirects'] : 4;
        $this->_headers = array();
    }
    
    public function addHeader($header) {
        $this->_headers[] = $header;
    }
    
    public function setGet($params) {
        $this->_post = false;
        if (is_array($params)) {
            $this->_url .= (strpos($this->_url, '?') === false ? '?' : '')
                . http_build_query($params);
        }
    }
    
    public function setPost($postFields) {
        $this->_post = true;
        if (is_array($postFields)) {
            $this->_postFields = http_build_query($postFields);
        }
    }

    public function exec($url = null) {
       if ($url) {
         $this->_url = $url;
       }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->_maxRedirects);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->_followlocation);

        if (count($this->_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
        }
        
        if ($this->_post) {
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $this->_postFields);
        }

        $this->_response = curl_exec($ch);
        $this->_info = curl_getinfo($ch);
        $this->_error = curl_error($ch);
        curl_close($ch);
        
        return $this->_response;
    }

    public function getInfo($key) {
        return $this->_info[$key];
    }
    public function getResponse() {
        return $this->_response;
    }
    public function getError() {
        return $this->_error;
    }
}