<?php namespace RubenArakelyan\ZomatoApi;

// **********************************************************************
// Zomato API PHP wrapper
// Version 1.0
// Author: Ruben Arakelyan <ruben@ra.me.uk>
//
// Copyright (C) 2015 Ruben Arakelyan.
// This file is licensed under the MIT licence.
//
// For more information, see https://github.com/rubenarakelyan/zomato-api-php
// **********************************************************************

/**
 * Class ZomatoApi
 * @package ZomatoApi
 */
class ZomatoApi
{
    // API key
    private $api_key;
    
    // Output type
    private $output_type;

    // cURL handle
    private $ch;

    // Default constructor
    public function __construct($api_key, $output_type)
    {
        // Check and set API key
        if (!$api_key)
        {
            throw new ZomatoApiException('No API key provided.');
        }
        
        if (!preg_match('/^[A-Za-z0-9]+$/', $api_key))
        {
            throw new ZomatoApiException('Invalid API key provided.');
        }
        
        $this->api_key = $api_key;
        
        // Define valid output types
        $valid_output_types = [
          'xml'   => 'application/xml',
          'json'  => 'application/json',
        ];
        
        // Check and set output type
        if (!$output_type || !array_key_exists($output_type, $valid_output_types))
        {
             throw new ZomatoApiException('Invalid output type: ' . $output_type . '. Please look at the documentation for supported output types.');
        }
        
        $this->output_type = $valid_output_types[$output_type];
        
        // Create a new instance of cURL
        $this->ch = curl_init();
        
        // Set the user agent
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Zomato API PHP wrapper (+https://github.com/rubenarakelyan/zomato-api-php)');
        
        // Return the result of the query
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        
        // Set custom headers for the API key and return format
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Accept: ' . $this->output_type, 'user_key: ' . $this->api_key]);
    }
    
    // Default destructor
    public function __destruct()
    {
        // Delete the instance of cURL
        curl_close($this->ch);
    }
    
    // Send an API query
    public function query($func, $args = [])
    {
        // Exit if any arguments are not defined
        if (!isset($func) || $func == '' || !isset($args) || $args == '' || !is_array($args))
        {
            throw new ZomatoApiException('Function name or arguments not provided.');
        }
        
        // Construct the query
        $query = new ZomatoApiRequest($func, $args, $this->api_key);
        
        // Execute the query
        if (is_object($query))
        {
            return $this->_execute_query($query);
        }
        else
        {
            throw new ZomatoApiException('Could not assemble request using ZomatoApiRequest.');
        }
    }
    
    // Execute an API query
    private function _execute_query($query)
    {
        // Make the final URL
        $url = $query->encode_arguments();
        
        // Set the URL
        curl_setopt($this->ch, CURLOPT_URL, $url);
        
        // Get the result
        $result = curl_exec($this->ch);
        
        // Find out if all is OK
        if (!$result)
        {
            // A problem happened with cURL
            throw new ZomatoApiException('cURL error occurred: ' . curl_error($this->ch));
        }
        else
        {
            $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($http_code == 404)
            {
                // Received a 404 error querying the API
                throw new ZomatoApiException('Could not reach Zomato server.');
            }
            
            return $result;
        }
    }
}

?>