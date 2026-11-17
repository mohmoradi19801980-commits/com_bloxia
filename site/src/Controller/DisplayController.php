<?php

namespace BastanGraphic\Component\Bloxia\Site\Controller;



use Joomla\CMS\MVC\Controller\BaseController;



class DisplayController extends BaseController

{

    // تنظیم ویوی پیش‌فرض کامپوننت روی صفحه لاگین

    protected $default_view = 'login';

    

    public function display($cachable = false, $urlparams = [])

    {

        return parent::display($cachable, $urlparams);

    }

}