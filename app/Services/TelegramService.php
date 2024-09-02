<?php
namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramService
{
    protected $bot;

    public function __construct()
    {
        // Инициализация бота, если нужно
        $this->bot = Telegram::bot('mybot');
    }

    public function setWebhook($url)
    {
        $response = $this->bot->setWebhook(['url' => $url]);

        return $response['ok'];
    }

    public function getBotInfo()
    {
        return $this->bot->getMe();
    }

    public function getWebhookUpdates()
    {
        return $this->bot->getWebhookUpdates();
    }

    // Здесь можно добавить другие методы для взаимодействия с Telegram API
}
