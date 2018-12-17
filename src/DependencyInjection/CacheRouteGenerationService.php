<?php

namespace Webcustoms\EnlightSymfonyWrapper\DependencyInjection;

use Enlight_Controller_Request_Request as Request;
use function str_replace;

class CacheRouteGenerationService extends \Shopware\Components\HttpCache\CacheRouteGenerationService
{
	/**
	 * @inheritDoc
	 */
	public function getControllerRoute(Request $request)
	{
		if ($request->getQuery('_matchInfo'))
		{
			$controllerName = str_replace('\\', '', $request->getQuery('_matchInfo')['_controller']);
			return implode('/', [
				strtolower($request->getModuleName()),
				strtolower($controllerName),
			]);
		}
		return parent::getControllerRoute($request);
	}
}