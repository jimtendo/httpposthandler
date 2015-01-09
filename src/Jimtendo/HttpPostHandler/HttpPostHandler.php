<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Logger;

/**
 * @author James Augustus Zuccon <zuccon@gmail.com>
 */
class HttpPostHandler extends AbstractProcessingHandler
{
    /**
     * @var string
     */
    protected $endPoint;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @param string  $apiKey  API Key to be supplied (Leave blank if not needed)
     */
    public function __construct($endPoint, $apiKey = '', $level = Logger::DEBUG, $bubble = true)
    {
        // Set the URL
        $this->endPoint = $endPoint;
    
        // Set the token
        $this->apiKey = $apiKey;
        
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $data = array();
        $data['api_key'] = $this->apiKey;
        $data['created_at'] = $record['datetime'];
        $data['code'] = $record['level'];
        
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $data['request_method'] = $_SERVER['REQUEST_METHOD'];
        }
        
        if (isset($_SERVER['REQUEST_URI'])) {
            $data['request_uri'] = $_SERVER['REQUEST_URI'];
        }
        
        if (isset($_SERVER['HTTP_REFERER'])) {
            $data['referrer'] = $_SERVER['HTTP_REFERER'];
        }
        
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
        
        $data['stack_trace'] = $record['formatted'];
    
        $this->toServer($this->endPoint, $data); 
    }
    
    private function toServer($endPoint, array $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endPoint); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data );
        $content = trim(curl_exec($ch));
        curl_close($ch);
        
        return $content;
    }
}
