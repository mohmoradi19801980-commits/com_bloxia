<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;
use BastanGraphic\Component\Bloxia\Administrator\Extension\BloxiaComponent;

return new class implements ServiceProviderInterface {
    public function register(Container $container) {
        $container->registerServiceProvider(new MVCFactory('\\BastanGraphic\\Component\\Bloxia'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\BastanGraphic\\Component\\Bloxia'));
        $container->registerServiceProvider(new RouterFactory('\\BastanGraphic\\Component\\Bloxia'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new BloxiaComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));
                return $component;
            }
        );
    }
};