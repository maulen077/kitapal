<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Participant;
use App\Models\User;
use App\Models\Book;
use App\Models\City;
use Telegram\Bot\Laravel\Facades\Telegram;

class MeetingController extends Controller
{
    public function createMeeting($chatId, $text)
    {
        // Разбор команды и параметров
        $parts = explode(' ', $text, 3);
        $bookId = $parts[1] ?? null;
        $description = $parts[2] ?? null;

        if (!$bookId || !$description) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Используйте команду в формате: /create_meeting [book_id] [description]'
            ]);
            return;
        }

        // Получение пользователя
        $user = User::where('chat_id', $chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы не зарегистрированы. Пожалуйста, зарегистрируйтесь сначала.'
            ]);
            return;
        }

        // Проверка наличия книги
        $book = Book::find($bookId);

        if (!$book) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Книга не найдена. Хотите добавить её? Напишите: /add_book [название книги]'
            ]);
            $user->awaiting_book = true; // добавляем флаг для ожидания названия книги
            $user->save();
            return;
        }

        // Проверка, не создавал ли пользователь встречу или не участвовал ли в ней
        $existingMeeting = Meeting::where('book_id', $bookId)
            ->whereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if ($existingMeeting) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы уже создавали или участвовали во встрече по этой книге.'
            ]);
            return;
        }

        // Создание новой встречи
        $meeting = new Meeting();
        $meeting->book_id = $bookId;
        $meeting->organizer_id = $user->id;
        $meeting->city_id = $user->city_id;
        $meeting->description = $description;
        $meeting->date_created = now();
        $meeting->save();

        // Добавление организатора в список участников
        Participant::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'has_attended' => false,
        ]);

        // Сообщение о создании встречи
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Встреча успешно создана!'
        ]);
    }


    public function addBook($chatId, $text)
    {
        $user = User::where('chat_id', $chatId)->first();

        if (!$user || !$user->awaiting_book) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Неожиданный ввод. Для добавления книги используйте команду: /add_book [название книги]'
            ]);
            return;
        }

        $bookTitle = trim(str_replace('/add_book', '', $text));

        if (empty($bookTitle)) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Пожалуйста, укажите название книги: /add_book [название книги]'
            ]);
            return;
        }

        // Добавление книги в базу данных
        $book = Book::create(['name' => $bookTitle]);

        // Очистка флага ожидания книги
        $user->awaiting_book = false;
        $user->save();

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Книга успешно добавлена! Теперь вы можете создать встречу с её участием.'
        ]);
    }

    public function joinMeeting($chatId, $text)
    {
        $parts = explode( '', $text, 2);
        $meetingId = $parts[1] ?? null;

        if (!$meetingId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Используйте команду в формате: /join_meeting [meeting_id]'
            ]);
            return;
        }

        // Получение пользователя
        $user = User::where('chat_id', $chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы не зарегистрированы. Пожалуйста, зарегистрируйтесь сначала.'
            ]);
            return;
        }

        // Проверка наличия встречи
        $meeting = Meeting::find($meetingId);

        if (!$meeting) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Встреча с таким идентификатором не найдена.'
            ]);
            return;
        }

        // Проверка, не участвовал ли пользователь в обсуждении этой книги ранее
        $existingParticipant = Participant::where('meeting_id', $meetingId)
            ->where('user_id', $user->id)
            ->first();

        if ($existingParticipant) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы уже участвуете в обсуждении этой книги.'
            ]);
            return;
        }

        // Добавление пользователя в список участников
        Participant::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'has_attended' => false,
        ]);

        // Отправка сообщения с деталями встречи
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Вы успешно присоединились к встрече! Детали встречи:\n' .
                'Книга: ' . $meeting->book->name . "\n" .
                'Место: ' . $meeting->description . "\n" .
                'Дата и время: ' . $meeting->created_at->format('d.m.Y H:i')
        ]);
    }

    public function myMeetings($chatId)
    {
        // Получение пользователя
        $user = User::where('chat_id', $chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы не зарегистрированы. Пожалуйста, зарегистрируйтесь сначала.'
            ]);
            return;
        }

        // Получение встреч, к которым присоединился пользователь
        $meetings = Meeting::whereHas('participants', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        if ($meetings->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы не присоединились ни к одной встрече.'
            ]);
            return;
        }

        $responseText = "Ваши встречи:\n";
        foreach ($meetings as $meeting) {
            $responseText .= "Книга: " . $meeting->book->name . "\n";
            $responseText .= "Место: " . $meeting->description . "\n";
            $responseText .= "Дата: " . $meeting->date_created->format('d.m.Y H:i') . "\n\n";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $responseText
        ]);
    }

    public function bookMeetings($chatId, $text)
    {
        // Разбор команды и параметров
        $parts = explode(' ', $text, 2);
        $bookId = $parts[1] ?? null;

        if (!$bookId) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Используйте команду в формате: /book_meetings [book_id]'
            ]);
            return;
        }

        // Получение пользователя
        $user = User::where('chat_id', $chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Вы не зарегистрированы. Пожалуйста, зарегистрируйтесь сначала.'
            ]);
            return;
        }

        // Проверка наличия книги
        $book = Book::find($bookId);

        if (!$book) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Книга с таким идентификатором не найдена.'
            ]);
            return;
        }

        // Получение встреч по книге в городе пользователя
        $meetings = Meeting::where('book_id', $bookId)
            ->where('city_id', $user->city_id)
            ->get();

        if ($meetings->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Нет доступных встреч для этой книги в вашем городе.'
            ]);
            return;
        }

        $responseText = "Встречи по книге '" . $book->name . "' в вашем городе:\n";
        foreach ($meetings as $meeting) {
            $responseText .= "ID встречи: " . $meeting->id . "\n";
            $responseText .= "Место: " . $meeting->description . "\n";
            $responseText .= "Дата: " . $meeting->date_created->format('d.m.Y H:i') . "\n\n";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $responseText
        ]);
    }
}
