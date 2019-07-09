<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * JSS Classic API interface class
 * @since 1.0.0
 */
 class PluginJamfAPIClassic {
    /** PluginJamfConnection object representing the connection to a JSS server */
    private static $connection;

    /**
     * Get data from a JSS Classic API endpoint.
     * @since 1.0.0
     * @param string  $endpoint The API endpoint.
     * @param bool    $raw If true, data is returned as JSON instead of decoded into an array.
     * @return mixed JSON string or associative array depending on the value of $raw.
     */
    private static function get(string $endpoint, $raw = false)
    {
        if (!self::$connection) {
            self::$connection = new PluginJamfConnection();
        }
        $url = (self::$connection)->getAPIUrl($endpoint);
        $curl = curl_init($url);
        // Set the username and password in an authentication header
        self::$connection->setCurlAuth($curl);
        curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
           'Content-Type: application/json',
           'Accept: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!$response) {
           return null;
        }
        if ($httpcode == 500) {
           $response = json_decode($response, true);
           if (isset($response['fault'])) {
              $fault = $response['fault'];
              switch ($fault['detail']['errorcode']) {
                 case 'policies.ratelimit.QuotaViolation':
                    // We are making too many API calls in a short time.
                    throw new RateLimitException($fault['faultstring']);
              }
           }
           throw new RuntimeException(__("Unknown JSS API Error"));
        } else {
           return ($raw ? $response : json_decode($response, true));
        }
    }

    /**
     * Construct a parameter query string for an API endpoint.
     * @since 1.0.0
     * @param array $params API inputs.
     * @return string The constructed parameter string.
     */
    private static function getParamString(array $params = [])
    {
        $param_str = "";
        foreach ($params as $key => $value) {
            $param_str = "{$param_str}/{$key}/{$value}";
        }
        return $param_str;
    }

    /**
     * Get data for a specified JSS itemtype and parameters.
     * @since 1.0.0
     * @param string $itemtype The type of data to fetch. This matches up with endpoint names.
     * @param array $params API input parameters such as udid, name, or subset.
     * @return array Associative array of the decoded JSON response.
     */
    public static function getItems(string $itemtype, array $params = [])
    {
        $param_str = self::getParamString($params);
        $endpoint = "$itemtype$param_str";
        $response = self::get($endpoint);
        // Strip first key (usually like mobile_devices or mobile_device)
        // No other first level keys exist
        return (!is_null($response) && count($response)) ? reset($response) : null;
    }
 }
