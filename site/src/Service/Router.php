<?php
namespace BastanGraphic\Component\Bloxia\Site\Service;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Menu\AbstractMenu;

class Router extends RouterView
{
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        // ۱. معرفی صفحه لاگین به موتور سئوی جوملا
        $login = new RouterViewConfiguration('login');
        $this->registerView($login);

        // ۲. معرفی صفحه کاراکتر به موتور سئوی جوملا
        $character = new RouterViewConfiguration('character');
        $this->registerView($character);

        // ۳. اگر در آینده صفحه world را ساختیم، این خطوط را از کامنت درمی‌آوریم:
        // $world = new RouterViewConfiguration('world');
        // $this->registerView($world);

        parent::__construct($app, $menu);
    }
}