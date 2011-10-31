<?php

/**
 * Eventbrite Package
 *
 * Eventbrite package for building web applications linked with Eventbrite's events and tickets system.
 *
 * @package		Eventbrite
 * @author		Gilles De Mey
 * @copyright           Copyright (c) 2011 Stas Sușcov
 * @license		See LICENSE
 * @link		http://www.gillesdemey.be
 */
/**
  The MIT License

  Copyright (c) 2011 Stas Sușcov

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
 */

namespace Eventbrite;

class Eventbrite
{

  /**
   * Application Key to access the data
   */
  protected $app_key;

  /**
   * User Key to identify
   */
  protected $user_key;

  /**
   * User email
   */
  protected $user;

  /**
   * User password
   */
  protected $password;

  /**
   * API URL to webservice
   */
  protected $api_url;

  /**
   * Error status
   */
  protected $error;

  /**
   * Default API URL
   */
  protected $default_api_url = "https://www.eventbrite.com/json/";

  /**
   * Force secured connection over SSL
   */
  protected $secure = false;

  /**
   * Construct
   * 
   * Called when the class is initialised
   * 
   * @access	protected
   * @return	Eventbrite\Eventbrite
   */
  protected function __construct()
  {
    // Load Config
    \Config::load('eventbrite', true);

    $this->app_key = \Config::get('eventbrite.app_key');
    $this->user_key = \Config::get('eventbrite.user_key');

    $this->secure = \Config::get('eventbrite.secure');

    $this->api_url = \Config::get('eventbrite.api_url');

    $this->setUrl($this->api_url);

    return $this;
  }

  /**
   * Factory
   * 
   * Creates new instance of class
   * 
   * @access	public
   * @return	Eventbrite\Eventbrite
   */
  public static function factory()
  {
    return new Eventbrite();
  }

  /**
   * Forge
   * 
   * Creates new instance of class
   * 
   * @access	public
   * @return	Eventbrite\Eventbrite
   */
  public static function forge()
  {
    return new Eventbrite();
  }

  /**
   * Define API URL
   *
   * @param String $url, the webservice uri to be used
   */
  function setUrl($url)
  {
    $this->api_url = parse_url($url);
    $this->checkSecure();
  }

  /**
   * Sets user email
   *
   * @param String $email, the email adress to be used
   */
  function setUser($email)
  {
    $this->user = $email;
  }

  /**
   * Sets user password
   *
   * @param String $pass, the password to be used
   */
  function setPassword($pass)
  {
    $this->password = $pass;
  }

  /**
   * Toggle secure connection
   *
   * @param Boolean $value, true or false
   */
  function checkSecure($value = true)
  {
    $this->secure = (bool) $value;

    if (!empty($this->api_url) and isset($this->api_url['scheme']))
      if (!$this->secure)
        $this->api_url['scheme'] = 'http';
      else
        $this->api_url['scheme'] = 'https';
  }

  /**
   * Checks for errors
   *
   * @return null on no errors, Mixed data on error
   */
  function getError()
  {
    return $this->error;
  }

  /**
   * Dynamic methods handler
   */
  function __call($method, $args)
  {
// Reset error status
    $this->error = null;

// Build query
    $query_data = array();

// Add auth details to query
    $query_data['app_key'] = $this->app_key;
    $query_data['user_key'] = $this->user_key;
    $query_data['user'] = $this->user;
    $query_data['password'] = $this->password;

// Parse args
    if (is_array($args))
      $args = reset($args);

// If method exists, try to match it's params
    if (is_array($args) and is_array($this->api_methods[$method])) {
      foreach ($this->api_methods[$method] as $k)
        if (array_key_exists($k, $args))
          $query_data[$k] = $args[$k];
    } else // Or, try an undefined method, you're on your own from here
      $query_data = array_merge_recursive($query_data, array($args));

// Build the http query
    $query_url = $this->api_url;
    $query_url['path'] .= $method . '?';



    // Query the API
    $http_query = $query_url['scheme'] . '://';
    unset($query_url['scheme']);
    $http_query .= implode('', $query_url);
    $http_query .= http_build_query($query_data, '', '&amp;');

    $response = file_get_contents($http_query);

    if ($response)
      $response = json_decode($response);

    if (isset($response->error))
      $this->error = $response->error;

    return $response;
  }

  /**
   * Definitions for dynamic methods
   *
   * @link http://developer.eventbrite.com/doc/
   */
  protected $api_methods = array(
      'discount_new' => array('event_id', 'code', 'amount_off', 'percent_off', 'tickets', 'quantity_available', 'start_date', 'end_date'),
      'discount_update' => array('id', 'code', 'amount_off', 'percent_off', 'tickets', 'quantity_available', 'start_date', 'end_date'),
      'event_copy' => array('event_id', 'event_name'),
      'event_get' => array('id'),
      'event_list_attendees' => array('id', 'count', 'page', 'do_not_display', 'show_full_barcodes'),
      'event_list_discounts' => array('id'),
      'event_new' => array('title', 'description', 'start_date', 'end_date', 'timezone', 'privacy', 'personalized_url', 'venue_id', 'organizer_id', 'capacity', 'currency', 'status', 'custom_header', 'custom_footer', 'background_color', 'text_color', 'link_color', 'title_text_color', 'box_background_color', 'box_text_color', 'box_border_color', 'box_header_background_color', 'box_header_text_color'),
      'event_search' => array('keywords', 'category', 'address', 'city', 'region', 'postal_code', 'country', 'within', 'within_unit', 'latitude', 'longitude', 'date', 'date_created', 'date_modified', 'organizer', 'max', 'count_only', 'sort_by', 'page', 'since_id', 'tracking_link'),
      'event_update' => array('event_id', 'title', 'description', 'start_date', 'end_date', 'timezone', 'privacy', 'personalized_url', 'venue_id', 'organizer_id', 'capacity', 'currency', 'status', 'custom_header', 'custom_footer', 'background_color', 'text_color', 'link_color', 'title_text_color', 'box_background_color', 'box_text_color', 'box_border_color', 'box_header_background_color', 'box_header_text_color'),
      'organizer_list_events' => array('id'),
      'organizer_new' => array('name', 'description'),
      'organizer_update' => array('id', 'name', 'description'),
      'payment_update' => array('event_id', 'accept_paypal', 'paypal_email', 'accept_google', 'google_merchant_id', 'google_merchant_key', 'accept_check', 'instructions_check', 'accept_cash', 'instructions_cash', 'accept_invoice', 'instructions_invoice'),
      'ticket_new' => array('event_id', 'is_donation', 'name', 'description', 'price', 'quantity', 'start_sales', 'end_sales', 'include_fee', 'min', 'max'),
      'ticket_update' => array('id', 'is_donation', 'name', 'description', 'price', 'quantity', 'start_sales', 'end_sales', 'include_fee', 'min', 'max', 'hide'),
      'user_get' => array('user_id', 'email'),
      'user_list_events' => array('user', 'do_not_display', 'event_statuses', 'asc_or_desc'),
      'user_list_organizers' => array('user', 'password'),
      'user_list_tickets' => array(),
      'user_list_venues' => array('user', 'password'),
      'user_new' => array('email', 'password'),
      'user_update' => array('new_email', 'new_password'),
      'venue_new' => array('organizer_id', 'venue', 'adress', 'adress_2', 'city', 'region', 'postal_code', 'country_code'),
      'venue_update' => array('id', 'venue', 'adress', 'adress_2', 'city', 'region', 'postal_code', 'country_code')
  );

}

?>