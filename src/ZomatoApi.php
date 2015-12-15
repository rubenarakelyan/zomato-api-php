<?php namespace ZomatoApi;

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
        $query = new Zomato_Request($func, $args, $this->api_key);
        
        // Execute the query
        if (is_object($query))
        {
            return $this->_execute_query($query);
        }
        else
        {
            throw new ZomatoApiException('Could not assemble request using Zomato_Request.');
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

class Zomato_Request
{
    // API URL
    private $url = 'https://developers.zomato.com/api/v2.1/';
    
    // Chosen function, arguments and API key
    private $func;
    private $args;
    
    // Default constructor
    public function __construct($func, $args, $api_key)
    {
        // Set function, arguments and API key
        $this->func = $func;
        $this->args = $args;
        $this->api_key = $api_key;
        
        // Get and set the URL
        $this->url = $this->_get_uri_for_function($this->func);
        
        // Check to see if valid URL has been set
        if (!isset($this->url) || $this->url == '')
        {
            throw new ZomatoApiException('Invalid function: ' . $this->func . '. Please look at the documentation for supported functions.');
        }
    }
    
    // Encode function arguments into a URL query string
    public function encode_arguments()
    {
        // Make sure all mandatory arguments for a particular function are present
        if (!$this->_validate_arguments($this->func, $this->args))
        {
            throw new ZomatoApiException('All mandatory arguments for ' . $this->func . ' not provided.');
        }
        
        // Assemble the URL
        $full_url = $this->url . '?key=' . $this->api_key . '&';
        foreach ($this->args as $name => $value)
        {
            $full_url .= $name . '=' . urlencode($value) . '&';
        }
        $full_url = substr($full_url, 0, -1);
        
        return $full_url;
    }
    
    // Get the URL for a particular function
    private function _get_uri_for_function($func)
    {
        // Exit if any arguments are not defined
        if (!isset($func) || $func == '')
        {
            return '';
        }
        
        // Define valid functions
        $valid_functions = [
          'categories'        => 'Get list of Categories',
          'cities'            => 'Get city details',
          'collections'       => 'Get Zomato collections in a city',
          'cuisines'          => 'Get list of all cuisines in a city',
          'establishments'    => 'Get list of restaurant types in a city',
          'geocode'           => 'Get location details based on coordinates',
          'location_details'  => 'Get Zomato location details',
          'locations'         => 'Search for locations',
          'restaurant'        => 'Get restaurant details',
          'reviews'           => 'Get restaurant reviews',
          'search'            => 'Search for restaurants',
        ];
        
        // If the function exists, return its URL
        if (array_key_exists($func, $valid_functions))
        {
            return $this->url . $func;
        }
        else
        {
            return '';
        }
    }
    
    // Validate arguments
    private function _validate_arguments($func, $args)
    {
        // Define mandatory arguments
        $functions_params = [
          'categories'        => [],
          'cities'            => [],
          'collections'       => [],
          'cuisines'          => [],
          'establishments'    => [],
          'geocode'           => ['lat', 'lon'],
          'location_details'  => ['entity_id', 'entity_type'],
          'locations'         => ['query'],
          'restaurant'        => ['res_id'],
          'reviews'           => ['res_id'],
          'search'            => [],
        ];
        
        // Check to see if all mandatory arguments are present
        $required_params = $functions_params[$func];
        foreach ($required_params as $param)
        {
            if (!isset($args[$param]))
            {
                return false;
            }
        }
        
        return true;
    }
}

?>