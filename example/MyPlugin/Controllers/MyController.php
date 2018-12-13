<?php

namespace MyPlugin\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/my-controller-path")
 */
class MyController
{
	/**
	 * @Route("/my-action-path")
	 * @return Response
	 */
	public function custom()
	{
		return new Response('Hello World');
	}
	
	/**
	 * @Route("/json")
	 * @return JsonResponse
	 */
	public function json()
	{
		return new JsonResponse(['status' => 'ok']);
	}
}