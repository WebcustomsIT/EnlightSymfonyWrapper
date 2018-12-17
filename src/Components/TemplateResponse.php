<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Enlight_Controller_Request_Request;
use Symfony\Component\HttpFoundation\Response;
use const PATHINFO_EXTENSION;

class TemplateResponse extends Response
{
	protected static $_defaults = [];
	
	private          $templateName;
	
	private          $variables;
	
	public function __construct($name, array $variables = [], $status = 200, array $headers = [])
	{
		$this->templateName = $name;
		$this->variables    = $variables;
		parent::__construct(null, $status, $headers);
	}
	
	/**
	 * @param array $variables
	 */
	public static function setDefaults(array $variables = [])
	{
		static::$_defaults = $variables;
	}
	
	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	public function assign($name, $value)
	{
		$this->variables[$name] = $value;
	}
	
	/**
	 * @return string
	 */
	public function getTemplateName()
	{
		return $this->templateName;
	}
	
	/**
	 * @param Enlight_Controller_Request_Request $request
	 *
	 * @return string
	 */
	public function getFullTemplateName(Enlight_Controller_Request_Request $request)
	{
		$name = rtrim($this->templateName, '/');
		
		$dir = trim($request->getQuery('_matchInfo')['_options']['template_dir'], '/');
		if ($dir && strpos($name, $dir) !== 0)
		{
			$name = "$dir/$name";
		}
		
		$suffix = $request->getQuery('_matchInfo')['_options']['template_suffix'];
		if (!$suffix)
		{
			$suffix = '.tpl';
		}
		
		$ext = pathinfo($name, PATHINFO_EXTENSION);
		if (".$ext" !== $suffix)
		{
			$name .= $suffix;
		}
		
		return $name;
	}
	
	/**
	 * @return array
	 */
	public function getVariables()
	{
		return array_merge(static::$_defaults, $this->variables);
	}
}