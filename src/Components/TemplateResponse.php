<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Symfony\Component\HttpFoundation\Response;

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
	 * @return mixed
	 */
	public function getTemplateName()
	{
		return $this->templateName;
	}
	
	/**
	 * @return array
	 */
	public function getVariables()
	{
		return array_merge(static::$_defaults, $this->variables);
	}
}