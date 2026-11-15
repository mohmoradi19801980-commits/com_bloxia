<?php
namespace BastanGraphic\Plugin\User\Bloxia\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Event\SubscriberInterface;

class BloxiaUserPlugin extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAfterLogin' => 'onUserAfterLogin',
        ];
    }

    public function onUserAfterLogin($options)
    {
        $app = Factory::getApplication();
        
        // در بخش مدیریت ریدایرکت نمی‌کنیم
        if ($app->isClient('administrator')) {
            return true;
        }

        $user = Factory::getUser();
        $db   = Factory::getContainer()->get('DatabaseDriver');

        // بررسی وضعیت کاربر در جدول اختصاصی Bloxia
        $query = $db->getQuery(true)
            ->select($db->quoteName('has_onboarded'))
            ->from($db->quoteName('#__bloxia_user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
            
        $db->setQuery($query);
        $hasOnboarded = $db->loadResult();

        // اگر کاربر رکورد نداشت، برایش رکورد اولیه می‌سازیم
        if ($hasOnboarded === null) {
            $profile = new \stdClass();
            $profile->user_id = $user->id;
            $profile->bloxia_points = 0;
            $profile->has_onboarded = 0;
            $db->insertObject('#__bloxia_user_profiles', $profile);
            $hasOnboarded = 0;
        }

        // تصمیم‌گیری برای مسیر ریدایرکت
        if ((int) $hasOnboarded === 0) {
            // هدایت به صفحه انتخاب کاراکتر
            $redirectUrl = Route::_('index.php?option=com_bloxia&view=character', false);
        } else {
            // هدایت به شهر/خانه سه بعدی
            $redirectUrl = Route::_('index.php?option=com_bloxia&view=world', false);
        }

        $app->enqueueMessage('به دنیای Bloxia خوش آمدید!', 'success');
        $app->redirect($redirectUrl);
    }
}