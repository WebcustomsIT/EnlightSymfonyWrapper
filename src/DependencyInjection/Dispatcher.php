<?php
/**
 * Copyright (c) 2016-2018 - Webcustoms IT Solutions GmbH
 * All rights reserved
 *
 * @Shopware\noEncryption
 */

namespace Webcustoms\EnlightSymfonyWrapper\DependencyInjection;

use Enlight_Controller_Action;
use Enlight_Controller_Dispatcher_Default;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Exception;
use Webcustoms\EnlightSymfonyWrapper\Components\ControllerWrapper;

class Dispatcher extends Enlight_Controller_Dispatcher_Default
{
	protected $inner;
	
	public function __construct(Enlight_Controller_Dispatcher_Default $inner)
	{
		$this->inner = $inner;
		parent::__construct();
	}
	
	public function addControllerDirectory($path, $module = null)
	{
		return $this->inner->addControllerDirectory($path, $module);
	}
	
	public function setControllerDirectory($directory, $module = null)
	{
		return $this->inner->setControllerDirectory($directory, $module);
	}
	
	public function getControllerDirectory($module = null)
	{
		return $this->inner->getControllerDirectory($module);
	}
	
	public function removeControllerDirectory($module)
	{
		return $this->inner->removeControllerDirectory($module);
	}
	
	public function addModuleDirectory($path)
	{
		return $this->inner->addModuleDirectory($path);
	}
	
	public function formatControllerName($unFormatted)
	{
		return $this->inner->formatControllerName($unFormatted);
	}
	
	public function formatActionName($unFormatted)
	{
		return $this->inner->formatActionName($unFormatted);
	}
	
	public function formatModuleName($unFormatted)
	{
		return $this->inner->formatModuleName($unFormatted);
	}
	
	public function setDefaultControllerName($controller)
	{
		return $this->inner->setDefaultControllerName($controller);
	}
	
	public function getDefaultControllerName()
	{
		return $this->inner->getDefaultControllerName();
	}
	
	public function setDefaultAction($action)
	{
		return $this->inner->setDefaultAction($action);
	}
	
	public function getDefaultAction()
	{
		return $this->inner->getDefaultAction();
	}
	
	public function setDefaultModule($module)
	{
		return $this->inner->setDefaultModule($module);
	}
	
	public function getDefaultModule()
	{
		return $this->inner->getDefaultModule();
	}
	
	public function getControllerClass(Enlight_Controller_Request_Request $request)
	{
		return $this->inner->getControllerClass($request);
	}
	
	public function getControllerPath(Enlight_Controller_Request_Request $request)
	{
		return $this->inner->getControllerPath($request);
	}
	
	public function getActionMethod(Enlight_Controller_Request_Request $request)
	{
		return $this->inner->getActionMethod($request);
	}
	
	public function getFullControllerName(Enlight_Controller_Request_Request $request)
	{
		return $this->inner->getFullControllerName($request);
	}
	
	public function getFullActionName(Enlight_Controller_Request_Request $request)
	{
		return $this->inner->getFullActionName($request);
	}
	
	public function isDispatchable(Enlight_Controller_Request_Request $request)
	{
		if ($request->getQuery('_matchInfo')['controller'] === ControllerWrapper::class)
		{
			return ControllerWrapper::class;
		}
		
		return false;
	}
	
	public function isValidModule($module)
	{
		return $this->inner->isValidModule($module);
	}
	
	public function dispatch(
		Enlight_Controller_Request_Request $request,
		Enlight_Controller_Response_Response $response
	)
	{
		$this->setResponse($response);
		
		if (!$this->isDispatchable($request))
		{
			$this->inner->dispatch($request, $response);
			return;
		}
		
		$proxy = Shopware()->Hooks()->getProxy(ControllerWrapper::class);
		
		/** @var $controller Enlight_Controller_Action */
		$controller = new $proxy($request, $response);
		$controller->setFront($this->Front());
		$controller->setContainer(Shopware()->Container());
		
		$request->setDispatched(true);
		
		$disableOb = $this->Front()->getParam('disableOutputBuffering');
		$obLevel   = ob_get_level();
		if (empty($disableOb))
		{
			ob_start();
		}
		
		try
		{
			$controller->dispatch($request->getQuery('_matchInfo')['_route']);
		}
		catch (Exception $e)
		{
			$curObLevel = ob_get_level();
			if ($curObLevel > $obLevel)
			{
				do
				{
					ob_get_clean();
					$curObLevel = ob_get_level();
				}
				while ($curObLevel > $obLevel);
			}
			throw $e;
		}
		
		if (empty($disableOb))
		{
			$content = ob_get_clean();
			$response->appendBody($content);
		}
	}
	
	protected function formatName($unFormatted, $isAction = false)
	{
		return $this->inner->formatName($unFormatted, $isAction);
	}
	
	public function setFront(Enlight_Controller_Front $controller)
	{
		return $this->inner->setFront($controller);
	}
	
	public function Front()
	{
		return $this->inner->Front();
	}
	
	public function setResponse(Enlight_Controller_Response_Response $response = null)
	{
		return $this->inner->setResponse($response);
	}
	
	public function Response()
	{
		return $this->inner->Response();
	}
}