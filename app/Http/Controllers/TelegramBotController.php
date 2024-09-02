<?php

namespace App\Http\Controllers;
use App\Http\Controllers\UserRegisterController;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;
use App\Services\TelegramService;

use Illuminate\Http\Request;

class TelegramBotController extends Controller
{

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request)
    {
        $update = $this->telegramService->getWebhookUpdates();
        $message = $update->getMessage();
        $text = $message->getText();
        $chatId = $message->getChat()->getId();
        $username = $message->getFrom()->getUsername();

        $botInfo = $this->telegramService->getBotInfo();

        \Log::info('Bot Info: ', $botInfo->toArray());

        if ($text === '/start') {
            return app(UserRegisterController::class)->startRegistration($chatId, $username);
        } elseif (strpos($text, '/create_meeting') === 0) {
            return app(MeetingController::class)->createMeeting($chatId, $text);
        } elseif (strpos($text, '/add_book') === 0) {
            return app(MeetingController::class)->addBook($chatId, $text);
        } elseif (strpos($text, '/join_meeting') === 0) {
            return app(MeetingController::class)->joinMeeting($chatId, $text);
        } elseif ($text === '/my_meetings') {
            return app(MeetingController::class)->myMeetings($chatId);
        } elseif (strpos($text, '/book_meetings') === 0) {
            return app(MeetingController::class)->bookMeetings($chatId, $text);
        }
    }

    public function setWebhook()
    {
        $url = 'https://your-domain.com/<token>/webhook';  // Замените на ваш реальный URL
        $success = $this->telegramService->setWebhook($url);

        if ($success) {
            return response()->json(['message' => 'Webhook was set successfully.']);
        } else {
            return response()->json(['message' => 'Failed to set webhook.'], 500);
        }
    }
}
