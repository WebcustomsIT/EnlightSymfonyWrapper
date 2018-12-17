<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\PreFilterInterface;

class PreFilter implements PreFilterInterface
{
	/**
	 * @param array   $params
	 * @param Context $context
	 *
	 * @return array
	 */
	public function preFilter($params, Context $context)
	{
		$matchInfo = (array)$context->getGlobalParam('_matchInfo');
		if ($matchInfo)
		{
			if (empty($params['module']))
			{
				$params['module'] = $matchInfo['_controller'];
				if (empty($params['controller']))
				{
					$params['controller'] = substr($params['module'], strrpos($params['module'], '\\') + 1);
				}
				$params['module'] = substr($params['module'], 0, strrpos($params['module'], '\\'));
			}
		}
		
		return $params;
	}
}