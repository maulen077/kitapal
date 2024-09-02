<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Keyboard\Keyboard;

class UserRegisterController extends Controller
{
    public function startRegistration($chatId, $username)
    {
        // Получаем список городов из базы данных
        $cities = City::pluck('name', 'id')->toArray();

        // Формируем кнопки с названиями городов
        $keyboard = Keyboard::make()
            ->inline()
            ->resize()
            ->oneTimeKeyboard()
            ->selective()
            ->addRow(array_map(function ($cityName, $cityId) {
                return Keyboard::inlineButton(['text' => $cityName, 'callback_data' => 'city_' . $cityId]);
            }, $cities, array_keys($cities)))
            ->addRow(Keyboard::inlineButton(['text' => 'Добавить город', 'callback_data' => 'add_city']));

        // Отправляем пользователю список городов
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Выберите ваш город из списка или добавьте новый:',
            'reply_markup' => $keyboard
        ]);
    }

    public function handleCallbackQuery(Request $request)
    {
        $callbackQuery = $request->callback_query;
        $chatId = $callbackQuery['message']['chat']['id'];
        $callbackData = $callbackQuery['data'];

        if (strpos($callbackData, 'city_') === 0) {
            $cityId = str_replace('city_', '', $callbackData);

            // Логика сохранения города для пользователя
            $city = City::find($cityId);
            $this->saveUserCity($chatId, $city);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Ваш город установлен: ' . $city->name,
            ]);
        } elseif ($callbackData === 'add_city') {
            // Запрашиваем у пользователя название нового города
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Введите название нового города:',
            ]);

            // Логика ожидания ввода названия нового города
            $request->session()->put('awaiting_city_name', $chatId);
        }
    }

    public function handleMessage(Request $request)
    {
        $message = $request->message;
        $chatId = $message['chat']['id'];
        $text = $message['text'];

        // Проверяем, ожидается ли от пользователя ввод города
        if ($request->session()->get('awaiting_city_name') == $chatId) {
            // Сохраняем новый город в базу данных
            $city = City::create(['name' => $text]);

            // Сохраняем город для пользователя
            $this->saveUserCity($chatId, $city);

            // Убираем сессию ожидания ввода города
            $request->session()->forget('awaiting_city_name');

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Город добавлен и выбран: ' . $city->name,
            ]);
        }
    }

    protected function saveUserCity($chatId, $city)
    {
        // Логика сохранения пользователя с городом
        User::updateOrCreate(
            ['chat_id' => $chatId],
            ['city_id' => $city->id]
        );
    }
}
