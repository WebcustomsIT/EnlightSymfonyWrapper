<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Enlight_Controller_Response_ResponseHttp;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use function array_map;
use function implode;

class ResponseWrapper extends Enlight_Controller_Response_ResponseHttp
{
	protected $response;
	
	public function __construct(Response $response)
	{
		$this->response = $response;
		$this->setBody($this->response->getContent());
	}
	
	/**
	 * Sets a cookie method
	 *
	 * @param string $name
	 * @param string $value
	 * @param int    $expire
	 * @param string $path
	 * @param string $domain
	 * @param bool   $secure
	 * @param bool   $httpOnly
	 *
	 * @return \Enlight_Controller_Response_Response* @link http://www.php.net/manual/de/function.setcookie.php
	 */
	public function setCookie(
		$name,
		$value = null,
		$expire = 0,
		$path = null,
		$domain = null,
		$secure = false,
		$httpOnly = false
	)
	{
		$this->response->headers->setCookie(
			new Cookie(
				$name,
				$value,
				$expire,
				$path,
				$domain,
				$secure,
				$httpOnly
			)
		);
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getCookies()
	{
		return array_map(
			function ($cookie)
			{
				/** @var Cookie $cookie */
				return [
					'name'     => $cookie->getName(),
					'value'    => $cookie->getValue(),
					'expire'   => $cookie->getExpiresTime(),
					'path'     => $cookie->getPath(),
					'domain'   => $cookie->getDomain(),
					'secure'   => $cookie->isSecure(),
					'httpOnly' => $cookie->isHttpOnly(),
				];
			},
			$this->response->headers->getCookies()
		);
	}
	
	/**
	 * Sends all cookies
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function sendCookies()
	{
		$this->response->sendHeaders();
		return $this;
	}
	
	/**
	 * Set a header
	 * If $replace is true, replaces any headers already defined with that
	 * $name.
	 *
	 * @param string  $name
	 * @param string  $value
	 * @param boolean $replace
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function setHeader($name, $value, $replace = false)
	{
		$this->response->headers->set($name, $value, $replace);
		return $this;
	}
	
	/**
	 * Return array of headers; see {@link $_headers} for format
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->response->headers->all();
	}
	
	/**
	 * Clear headers
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function clearHeaders()
	{
		$keys = $this->response->headers->keys();
		foreach ($keys as $key)
		{
			$this->response->headers->remove($key);
		}
		return $this;
	}
	
	/**
	 * Clears the specified HTTP header
	 *
	 * @param  string $name
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function clearHeader($name)
	{
		$this->response->headers->remove($name);
		return $this;
	}
	
	/**
	 * Set raw HTTP header
	 * Allows setting non key => value headers, such as status codes
	 *
	 * @param string $value
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function setRawHeader($value)
	{
		$delimiter = strpos($value, ':');
		$key       = substr($value, 0, $delimiter);
		$value     = substr($value, $delimiter + 1);
		$this->response->headers->set($key, $value);
		return $this;
	}
	
	/**
	 * Retrieve all {@link setRawHeader() raw HTTP headers}
	 *
	 * @return array
	 */
	public function getRawHeaders()
	{
		$headers = $this->response->headers->all();
		$raw     = [];
		foreach ($headers as $key => $values)
		{
			$raw[] = "$key: $values";
		}
		return $raw;
	}
	
	/**
	 * Clear all {@link setRawHeader() raw HTTP headers}
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function clearRawHeaders()
	{
		$this->clearAllHeaders();
		return $this;
	}
	
	/**
	 * Clears the specified raw HTTP header
	 *
	 * @param  string $headerRaw
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function clearRawHeader($headerRaw)
	{
		$delimiter = strpos($headerRaw, ':');
		$key       = substr($headerRaw, 0, $delimiter);
		$this->clearHeader($key);
		return $this;
	}
	
	/**
	 * Clear all headers, normal and raw
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function clearAllHeaders()
	{
		$this->clearHeaders();
		return $this;
	}
	
	/**
	 * Set HTTP response code to use with headers
	 *
	 * @param int $code
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function setHttpResponseCode($code)
	{
		parent::setHttpResponseCode($code);
		$this->response->setStatusCode($code);
		return $this;
	}
	
	/**
	 * Retrieve HTTP response code
	 *
	 * @return int
	 */
	public function getHttpResponseCode()
	{
		return $this->response->getStatusCode();
	}
	
	/**
	 * Send all headers
	 * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
	 * has been specified, it is sent with the first header.
	 *
	 * @return \Enlight_Controller_Response_Response
	 */
	public function sendHeaders()
	{
		$this->response->sendHeaders();
		return $this;
	}
	
	/**
	 * Echo the body segments
	 */
	public function outputBody()
	{
		$this->response->setContent(implode('', $this->_body));
		$this->response->sendContent();
	}
	
	/**
	 * Send the response, including all headers, rendering exceptions if so
	 * requested.
	 */
	public function sendResponse()
	{
		if ($this->isException() && $this->renderExceptions())
		{
			// TODO render exceptions instead
		}
		
		$this->sendHeaders();
		$this->outputBody();
	}
}