<?php


namespace Webcustoms\EnlightSymfonyWrapper\Components;


use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\GeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

class Generator implements GeneratorInterface
{
    protected $matcher;

    public function __construct(Matcher $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * @param array|string $params
     * @param Context $context
     *
     * @return array|string
     */
    public function generate(array $params, Context $context)
    {
        if ($params[$context->getControllerKey()] !== ControllerWrapper::class)
        {
            return null;
        }

        // TODO transform from $context instead (!)
        $symfonyContext = new RequestContext();
        $symfonyContext->fromRequest(Request::createFromGlobals());
        $symfonyContext->setHost($context->getHost());
        $symfonyContext->setBaseUrl($context->getBaseUrl());

        $generator = new UrlGenerator(
            $this->matcher->getRoutes(),
            $symfonyContext
        );

        $matchInfo = (array)$context->getGlobalParam('_matchInfo');
print_r($this->matcher->getRoutes()->all());

        // default
        $symfonyController = $matchInfo['_controller'];

        $this->matcher->getRoutes()->all()[0]->getOptions()["_controller"];

print_r($symfonyController);
        print_r($context);
die();
        var_dump($generator->generate("???", $params));
        return null;
    }
}