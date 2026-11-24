<?php
namespace BastanGraphic\Component\Bloxia\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use BastanGraphic\Component\Bloxia\Administrator\Helper\BloxiaHelper;

if (!class_exists(BloxiaHelper::class)) {
    $helperPath = JPATH_ADMINISTRATOR . '/components/com_bloxia/src/Helper/BloxiaHelper.php';
    if (is_file($helperPath)) {
        require_once $helperPath;
    }
}

class BankController extends BaseController
{
    public function createOrder()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false, 'message' => 'لطفاً ابتدا وارد حساب کاربری شوید.']);
            exit;
        }

        $input    = json_decode(file_get_contents('php://input'), true) ?: [];
        $pkgKey   = (string) ($input['package_key'] ?? '');
        $forceNew = !empty($input['force_new']);
        $packages = BloxiaHelper::getBankPackages();

        if (!isset($packages[$pkgKey])) {
            echo json_encode(['success' => false, 'message' => 'پکیج انتخابی نامعتبر است.']);
            exit;
        }

        $settings = BloxiaHelper::getBankSettings();
        if (trim($settings->card_number) === '') {
            echo json_encode(['success' => false, 'message' => 'شماره کارت بانکی هنوز توسط ادمین تنظیم نشده است.']);
            exit;
        }

        $db  = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();

        // منقضی کردن سفارش‌های قدیمی‌تر از ۱۰ دقیقه
        $expQ = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_bank_orders'))
            ->set($db->quoteName('status') . ' = ' . $db->quote('expired'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'))
            ->where($db->quoteName('expires_at') . ' < ' . $db->quote($now));
        $db->setQuery($expQ)->execute();

        // اگر کاربر همین الان یک سفارش معتبر برای همین پکیج دارد و force_new نیست
        if (!$forceNew) {
            $existQ = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__bloxia_bank_orders'))
                ->where($db->quoteName('user_id') . ' = ' . (int) $user->id)
                ->where($db->quoteName('package_key') . ' = ' . $db->quote($pkgKey))
                ->where($db->quoteName('status') . ' = ' . $db->quote('pending'))
                ->where($db->quoteName('expires_at') . ' >= ' . $db->quote($now))
                ->order($db->quoteName('id') . ' DESC');
            $db->setQuery($existQ, 0, 1);
            $existing = $db->loadObject();
            if ($existing) {
                $rem = max(0, strtotime($existing->expires_at) - strtotime($now));
                echo json_encode([
                    'success' => true,
                    'order'   => $this->formatOrder($existing, $settings, $rem),
                ]);
                exit;
            }
        }

        // منقضی کردن سفارش‌های معلق قبلی همین کاربر تا فقط یک عدد رندوم فعال داشته باشد
        $oldQ = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_bank_orders'))
            ->set($db->quoteName('status') . ' = ' . $db->quote('expired'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id)
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'));
        $db->setQuery($oldQ)->execute();

        // پیدا کردن کدهای رندوم ۲ یا ۳ رقمی که در ۱۰ دقیقه اخیر در حال استفاده هستند
        $usedQ = $db->getQuery(true)
            ->select($db->quoteName('random_code'))
            ->from($db->quoteName('#__bloxia_bank_orders'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'))
            ->where($db->quoteName('expires_at') . ' >= ' . $db->quote($now));
        $db->setQuery($usedQ);
        $usedCodes = array_map('intval', $db->loadColumn() ?: []);

        $randomCode = random_int(11, 999);
        for ($i = 0; $i < 40; $i++) {
            $candidate = random_int(11, 999);
            if (!in_array($candidate, $usedCodes, true)) {
                $randomCode = $candidate;
                break;
            }
        }

        $pkg        = $packages[$pkgKey];
        $baseToman  = (int) $pkg['base_toman'];
        $totalToman = $baseToman + $randomCode;
        $expiresAt  = Factory::getDate('+10 minutes')->toSql();

        $insQ = $db->getQuery(true)
            ->insert($db->quoteName('#__bloxia_bank_orders'))
            ->columns($db->quoteName([
                'user_id', 'package_key', 'bitblox_amount', 'base_toman',
                'random_code', 'total_toman', 'status', 'created_at', 'expires_at'
            ]))
            ->values(implode(',', [
                (int) $user->id,
                $db->quote($pkgKey),
                (int) $pkg['bitblox'],
                $baseToman,
                $randomCode,
                $totalToman,
                $db->quote('pending'),
                $db->quote($now),
                $db->quote($expiresAt),
            ]));
        $db->setQuery($insQ)->execute();
        $orderId = (int) $db->insertid();

        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_bank_orders'))->where('id = ' . $orderId));
        $orderRow = $db->loadObject();

        echo json_encode([
            'success' => true,
            'order'   => $this->formatOrder($orderRow, $settings, 600),
        ]);
        exit;
    }

    public function checkOrder()
    {
        header('Content-Type: application/json');
        $user = Factory::getUser();
        if ($user->guest) {
            echo json_encode(['success' => false]);
            exit;
        }

        $app     = Factory::getApplication();
        $orderId = $app->input->getInt('order_id', 0);

        BloxiaHelper::ensureTables();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();

        $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__bloxia_bank_orders'))->where('id = ' . $orderId . ' AND user_id = ' . (int) $user->id));
        $order = $db->loadObject();
        if (!$order) {
            echo json_encode(['success' => false]);
            exit;
        }

        if ($order->status === 'pending' && $order->expires_at < $now) {
            $order->status = 'expired';
            $db->setQuery($db->getQuery(true)->update($db->quoteName('#__bloxia_bank_orders'))->set($db->quoteName('status') . ' = ' . $db->quote('expired'))->where('id = ' . $orderId))->execute();
        }

        $rem = ($order->status === 'pending') ? max(0, strtotime($order->expires_at) - strtotime($now)) : 0;

        $db->setQuery($db->getQuery(true)->select('bloxia_points')->from($db->quoteName('#__bloxia_user_profiles'))->where('user_id = ' . (int) $user->id));
        $balance = (int) $db->loadResult();

        echo json_encode([
            'success'           => true,
            'status'            => $order->status,
            'remaining_seconds' => $rem,
            'bitblox_amount'    => (int) $order->bitblox_amount,
            'new_balance'       => $balance,
        ]);
        exit;
    }

    /**
     * Webhook دریافت پیامک بانکی از برنامه SMS Forwarder گوشی
     */
    public function smsWebhook()
    {
        header('Content-Type: application/json');
        $app      = Factory::getApplication();
        $settings = BloxiaHelper::getBankSettings();

        $rawBody = file_get_contents('php://input');
        $json    = $rawBody ? json_decode($rawBody, true) : null;

        $providedKey = $app->input->getString('key', '');
        if ($providedKey === '' && is_array($json) && !empty($json['key'])) {
            $providedKey = (string) $json['key'];
        }
        if ($providedKey === '' && !empty($_SERVER['HTTP_X_BLOXIA_KEY'])) {
            $providedKey = (string) $_SERVER['HTTP_X_BLOXIA_KEY'];
        }

        if ($settings->sms_secret === '' || !hash_equals($settings->sms_secret, $providedKey)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid secret key']);
            exit;
        }

        $smsText = '';
        if (is_array($json)) {
            $smsText = (string) ($json['text'] ?? $json['message'] ?? $json['msg'] ?? $json['body'] ?? $json['content'] ?? '');
        }
        if ($smsText === '') {
            $smsText = (string) ($app->input->getString('text', '') ?: $app->input->getString('message', '') ?: $app->input->getString('msg', ''));
        }

        if (trim($smsText) === '') {
            echo json_encode(['success' => false, 'message' => 'Empty SMS text']);
            exit;
        }

        // تبدیل اعداد فارسی/عربی به انگلیسی و حذف جداکننده‌ها
        $normalized = strtr($smsText, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            ',' => '', '٬' => '', '،' => '',
        ]);

        // اگر پیامک واضحاً مربوط به برداشت باشد، نادیده بگیر
        if (mb_stripos($normalized, 'برداشت') !== false && mb_stripos($normalized, 'واریز') === false) {
            echo json_encode(['success' => false, 'message' => 'Withdrawal SMS ignored']);
            exit;
        }

        preg_match_all('/\d{5,12}/', $normalized, $matches);
        $numbers = array_map('intval', $matches[0] ?? []);

        if (empty($numbers)) {
            echo json_encode(['success' => false, 'message' => 'No amount found in SMS']);
            exit;
        }

        $db  = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();

        // جستجو در سفارش‌های معتبر ۱۰ دقیقه اخیر
        $q = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bloxia_bank_orders'))
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'))
            ->where($db->quoteName('expires_at') . ' >= ' . $db->quote($now))
            ->order($db->quoteName('id') . ' DESC');
        $db->setQuery($q);
        $pendingOrders = $db->loadObjectList() ?: [];

        $matchedOrder = null;
        foreach ($pendingOrders as $ord) {
            $toman = (int) $ord->total_toman;
            $rial  = $toman * 10;
            if (in_array($toman, $numbers, true) || in_array($rial, $numbers, true)) {
                $matchedOrder = $ord;
                break;
            }
        }

        if (!$matchedOrder) {
            echo json_encode(['success' => false, 'message' => 'No matching pending order found']);
            exit;
        }

        // ثبت اتمیک پرداخت تا از شارژ تکراری جلوگیری شود
        $upd = $db->getQuery(true)
            ->update($db->quoteName('#__bloxia_bank_orders'))
            ->set($db->quoteName('status') . ' = ' . $db->quote('paid'))
            ->set($db->quoteName('paid_at') . ' = ' . $db->quote($now))
            ->set($db->quoteName('sms_raw') . ' = ' . $db->quote(mb_substr($smsText, 0, 1000)))
            ->where('id = ' . (int) $matchedOrder->id)
            ->where($db->quoteName('status') . ' = ' . $db->quote('pending'));
        $db->setQuery($upd)->execute();

        if ($db->getAffectedRows() > 0) {
            BloxiaHelper::creditUserBitblox(
                (int) $matchedOrder->user_id,
                (int) $matchedOrder->bitblox_amount,
                'bank_topup',
                'شارژ خودکار کارت به کارت با SMS Forwarder (مبلغ: ' . number_format((int) $matchedOrder->total_toman) . ' تومان)'
            );
            echo json_encode([
                'success'  => true,
                'order_id' => (int) $matchedOrder->id,
                'user_id'  => (int) $matchedOrder->user_id,
                'credited' => (int) $matchedOrder->bitblox_amount,
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Already processed']);
        exit;
    }

    private function formatOrder($row, $settings, int $remainingSeconds): array
    {
        return [
            'id'                => (int) $row->id,
            'package_key'       => $row->package_key,
            'bitblox_amount'    => (int) $row->bitblox_amount,
            'base_toman'        => (int) $row->base_toman,
            'random_code'       => (int) $row->random_code,
            'total_toman'       => (int) $row->total_toman,
            'total_rial'        => (int) $row->total_toman * 10,
            'status'            => $row->status,
            'expires_at'        => $row->expires_at,
            'remaining_seconds' => $remainingSeconds,
            'card_number'       => $settings->card_number,
            'card_holder'       => $settings->card_holder,
        ];
    }
}
