<?php
namespace BastanGraphic\Component\Bloxia\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\User\User;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;

class AuthController extends BaseController
{
    public function requestOtp()
    {
        Session::checkToken() or die('Invalid Token');
        $app = Factory::getApplication();
        $mobile = $app->input->post->getString('mobile', '');

        if (empty($mobile)) {
            echo new JsonResponse(null, 'شماره موبایل نامعتبر است', true);
            $app->close();
        }

        // تولید کد 4 رقمی رندوم
        $otp = rand(1000, 9999);
        
        // ذخیره در سشن برای مرحله بعد
        $session = Factory::getSession();
        $session->set('bloxia_temp_mobile', $mobile);
        $session->set('bloxia_temp_otp', $otp);

        // ارسال کد به فرانت‌اند (فقط برای فاز تستی/Mock)
        echo new JsonResponse(['mock_code' => $otp]);
        $app->close();
    }

    public function verifyOtp()
    {
        Session::checkToken() or die('Invalid Token');
        $app = Factory::getApplication();
        $userOtp = $app->input->post->getString('otp', '');
        
        $session = Factory::getSession();
        $savedOtp = $session->get('bloxia_temp_otp');
        $mobile = $session->get('bloxia_temp_mobile');

        if ($userOtp != $savedOtp) {
            echo new JsonResponse(null, 'کد وارد شده اشتباه است', true);
            $app->close();
        }

        // کد درست بود، حالا کاربر را لاگین یا ثبت‌نام می‌کنیم
        $db = Factory::getContainer()->get('DatabaseDriver');
        
        // آیا این شماره موبایل قبلا ثبت‌نام کرده؟ (فرض می‌کنیم username همان موبایل است)
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = ' . $db->quote($mobile));
        $db->setQuery($query);
        $userId = $db->loadResult();

        if (!$userId) {
            // ثبت نام کاربر جدید در جوملا
            $user = new User();
            $user->name = 'کاربر ' . substr($mobile, -4); // اسم موقت
            $user->username = $mobile;
            $user->email = $mobile . '@bloxia.local'; // ایمیل فیک اجباری جوملا
            $user->password = md5(time() . $mobile); // پسورد رندوم چون با OTP وارد می‌شوند
            $user->set('groups', [2]); // گروه 2 یعنی Registered
            $user->save();
            $userId = $user->id;
        } else {
            $user = User::getInstance($userId);
        }

        // لاگین کردن برنامه‌نویسی شده کاربر
        PluginHelper::importPlugin('user');
        $app->triggerEvent('onUserLogin', [
            ['username' => $user->username],
            ['action' => 'core.login.site']
        ]);
        
        $session->set('user', $user);
        
        // پاک کردن سشن‌های موقت OTP
        $session->clear('bloxia_temp_mobile');
        $session->clear('bloxia_temp_otp');

        // پس از رفرش، پلاگینی که قبلا نوشتیم کاربر را به صفحه درست می‌فرستد
        echo new JsonResponse(['redirect' => Route::_('index.php', false)]);
        $app->close();
    }
}