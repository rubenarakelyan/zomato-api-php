# zomato-api-php

PHP wrapper for the Zomato API.

## Usage

    use RubenArakelyan\ZomatoApi;
    
    // Include the API wrapper
    require_once 'src/ZomatoApi.php';
    
    // Set up a new instance of the API wrapper
    $zomatoapi = new ZomatoApi('[API KEY HERE]', 'json');
    
    // Get a list of cities called "London" in JSON format
    $cities = $zomatoapi->query('cities', ['q' => 'London']);
    
    // Print out the list
    header('Content-type: application/json');
    echo $cities;

## Options

`void ZomatoApi ( string $api_key , string $output_type )`

* `$api_key`: Your unique API key, obtained from Zomato.
* `$output_type`: Either `xml` or `json`.

`mixed query ( string $func [, array $args = [] ] )`

* `$func`: The API function to execute.
* `$args`: (Optional) Any data to pass to the API function, as an array of keys and values.

See https://developers.zomato.com/documentation for details of available functions and arguments.

## Error messages

* No API key provided: No API key was provided to the constructor.
* Invalid API key provided: The API key provided does not meet the expected format.
* Invalid output type: [output type]. Please look at the documentation for supported output types: The output type provided is not recognised as valid.
* Function name or arguments not provided: Either or both of the function and/or arguments were provided to the `query` method.
* Could not assemble request using ZomatoApi_Request: A code error occurred while attempting to construct the request to send.
* cURL error occurred: [error message]: There was a problem when trying to contact the site; the error message will provide more details.
* Could not reach Zomato server: A 404 error was encountered when attempting to contact the site.
* Invalid function: [function name]. Please look at the documentation for supported functions: The function provided is not recognised as valid.
* All mandatory arguments for [function name] not provided: One or more mandatory arguments for the selected function were not provided.

## Support

Please submit issues to https://github.com/rubenarakelyan/zomato-api-php/issues.

## Contributing

All pull requests are gratefully accepted.

## Licence

All files in this repository are licenced under the MIT licence.

Please note that data pulled by the API is licenced separately.