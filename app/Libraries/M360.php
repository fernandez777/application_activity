<?php

namespace App\Libraries;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;

class M360
{
	protected $url;
	protected $username;
	protected $password;
	protected $shortcode_mask;
	protected $content;
	protected $msisdn;
	protected $rcvd_transid;
	protected $transid;
	protected $timestamp;
	protected $response_code;
	protected $response_name;
	protected $response_message;
	protected $success;
	protected $environment;

	public function __construct()
	{
		$this->url              = new Uri(config('app.m360_host'));
		$this->username 		= config('app.m360_username');
		$this->password 		= config('app.m360_password');
		$this->shortcode_mask 	= config('app.m360_shortcode_mask');
		$this->success 			= false;
		$this->environment 		= config('app.m360_env');
	}

	public function get_url(){ return $this->url; }
	public function set_url($var){ $this->url = $var; }

	public function get_username(){ return $this->username; }
	public function set_username($var){ $this->username = $var; }

	public function get_password(){ return $this->password; }
	public function set_password($var){ $this->password = $var; }

	public function get_shortcode_mask(){ return $this->shortcode_mask; }
	public function set_shortcode_mask($var){ $this->shortcode_mask = $var; }

	public function get_content(){ return $this->content; }
	public function set_content($var){ $this->content = $var; }

	public function get_msisdn(){ return $this->msisdn; }
	public function set_msisdn($var){ $this->msisdn = $var; }

	public function get_rcvd_transid(){ return $this->rcvd_transid; }
	public function set_rcvd_transid($var){ $this->rcvd_transid = $var; }

	public function get_transid(){ return $this->transid; }
	public function set_transid($var){ $this->transid = $var; }

	public function get_timestamp(){ return $this->timestamp; }
	public function set_timestamp($var){ $this->timestamp = $var; }

	public function get_response_code(){ return $this->response_code; }
	public function set_response_code($var){ $this->response_code = $var; }

	public function get_response_name(){ return $this->response_name; }
	public function set_response_name($var){ $this->response_name = $var; }

	public function get_response_message(){ return $this->response_message; }
	public function set_response_message($var){ $this->response_message = $var; }

	public function success(){ return $this->success; }


	public function send()
	{
		$params = [
			'username' 			=> $this->get_username(),
			'password' 			=> $this->get_password(),
			'msisdn' 			=> $this->environment == 'TEST' ? env("M360_TEST_MOBILE", '09059424678') : $this->get_msisdn(),
			'content' 			=> $this->get_content(),
			'shortcode_mask' 	=> $this->get_shortcode_mask(),
			'rcvd_transid' 		=> $this->get_rcvd_transid(),
		];

		if($this->environment != 'LOCAL')
		{

			$client = new Client();
			try
			{
		        $response = $client->post($this->url->withQuery(http_build_query($params)));
		        $this->setResponse($response);
		    }
		    catch (RequestException $e) 
		    {
			    if ($e->hasResponse()) {
			        $response = json_decode($e->getResponse()->getBody()->getContents());

			    	$this->set_response_code($response->code);
			    	$this->set_response_name($response->name);

			        if(isset($response->message))
	        			$this->set_response_message($response->message);
			    }
			}
		}
		else
		{
			$this->fakeResponse();
		}
	}

	public function setResponse($response)
	{
        $result = json_decode($response->getBody()->getContents());

    	$this->set_response_code($result->code);
    	$this->set_response_name($result->name);

        if($result->code == 201)
        {
        	$this->set_transid($result->transid);
        	$this->set_timestamp($result->timestamp);

        	$this->success = true;
        }
        else
        {
        	if(isset($result->message))
        		$this->set_response_message($result->message);
        }
	}

	public function fakeResponse()
	{
		$this->set_response_code(201);
		$this->set_response_name('Created');
		$this->set_transid('testing');
		$this->set_timestamp(date('YmdHis'));
		$this->success = true;
	}




}