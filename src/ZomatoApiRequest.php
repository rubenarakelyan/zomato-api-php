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
 * Class ZomatoApiRequest
 * @package ZomatoApi
 */
class ZomatoApiRequest
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