<?php

namespace Webcustoms\EnlightSymfonyWrapper\Subscriber;

use Enlight\Event\SubscriberInterface;

class RouterOverrider implements SubscriberInterface
{
	protected $dispatcher;
	
	public function __construct($dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}
	
	public static function getSubscribedEvents()
	{
		return [
			'Enlight_Controller_Front_StartDispatch' => 'onStartDispatch'
		];
	}
	
	/**
	 * @param \Enlight_Event_EventArgs $args
	 *
	 * @throws \Enlight_Exception
	 */
	public function onStartDispatch(\Enlight_Event_EventArgs $args)
	{
		/** @var \Enlight_Controller_Front $front */
		$front = $args->get('subject');
		$front->setDispatcher($this->dispatcher);
	}
}