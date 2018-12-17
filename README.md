# Enlight-Symfony-Wrapper

## What does it do?
It allows you to use the `Route` annotation tags provided by the Symfony framework within your Shopware plugins.

## Installation
```bash
composer require webcustoms/enlight-symfony-wrapper
```

## Example usage
See the `example` directory for a working example.

## How do I

### ... implement `CSRFWhitelistAware`?
Simply implement it in your class definition, and we'll pick it up.

### ... generate URLs to my action?
```php
$this->container->get('router')->assemble([
    'module' => 'Your\Name\Space', // optional if it's the current one
    'controller' => 'YourClassName', // optional if it's the current one
    'action' => 'yourMethodName'
]);
```
or
```php
$this->container->get('router')->assemble([
    // You can also set 'route' directly, either the auto-generated
    // name from Symfony, or the "name" attribute you set manually
    // for your route.
   'route' => 'your_name_space.your_class_name.your_method_name'
]);
```

### ... do something on preDispatch or postDispatch?
By subscribing to KernelEvents as described in the article
[How to Set Up Before and After Filters](https://symfony.com/doc/3.4/event_dispatcher/before_after_filters.html) by Symfony.

The following Shopware-like events are notified, in this order:
- `Enlight_Controller_Action_PreDispatch`
- `Enlight_Controller_Action_PreDispatch_Backend` (or `_Api`, `_Frontend`, `_Widgets` depending on the URL)
- `Enlight_Controller_Action_PreDispatch_MyNameSpace\MyController`
- ~~`Enlight_Controller_Action_Backend_WebcustomsEnlightSymfonyWrapperComponentsControllerWrapper_MyMethodName`~~ (which won't be that helpful)
- `PreDispatch_mynamespace_mycontroller_myaction`
- `PreDispatch_MyNameSpace\MyController::MyAction`
- `Dispatch_mynamespace_mycontroller_myaction` (notifyUntil)
- `Dispatch_MyNameSpace\MyController::MyAction` (notifyUntil)
- `PostDispatchSecure_mynamespace_mycontroller_myaction`
- `PostDispatchSecure_MyNameSpace\MyController::MyAction`
- `PostDispatch_mynamespace_mycontroller_myaction`
- `PostDispatch_MyNameSpace\MyController::MyAction`
- `Enlight_Controller_Action_PostDispatchSecure_MyNameSpace\MyController`
- `Enlight_Controller_Action_PostDispatchSecure_Backend` (or `_Api`, `_Frontend`, `_Widgets` depending on the URL)
- `Enlight_Controller_Action_PostDispatchSecure`
- `Enlight_Controller_Action_PostDispatch_MyNameSpace\MyController`
- `Enlight_Controller_Action_PostDispatchS_Backend` (or `_Api`, `_Frontend`, `_Widgets` depending on the URL)
- `Enlight_Controller_Action_PreDispatch`
