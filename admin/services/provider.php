<?php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use BastanGraphic\Component\Bloxia\Administrator\Extension\BloxiaComponent;

return new class implements ServiceProviderInterface {
    public function register(Container $container) {
        $container->registerServiceProvider(new MVCFactory('\\BastanGraphic\\Component\\Bloxia'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\BastanGraphic\\Component\\Bloxia'));
        $container->registerServiceProvider(new RouterFactory('\\BastanGraphic\\Component\\Bloxia'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new BloxiaComponent($container->get('ComponentDispatcherFactory'));
                $component->setRegistry($container->get('Registry'));
                $component->setMVCFactory($container->get('MVCFactory'));
                $component->setRouterFactory($container->get('RouterFactory'));
                return $component;
            }
        );
    }
};