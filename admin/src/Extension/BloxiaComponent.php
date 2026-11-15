<?php
namespace BastanGraphic\Component\Bloxia\Administrator\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

// با اضافه کردن Interface و Trait، قابلیت مسیریابی را به کامپوننت می‌دهیم
class BloxiaComponent extends MVCComponent implements RouterServiceInterface
{
    use RouterServiceTrait;
}