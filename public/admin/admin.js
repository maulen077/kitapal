
// $(document).ready(function () {
//     $(document).on('click', '.duplicate-answer', function () {
//         var $card = $(this).closest('.card'); // Найти ближайший элемент с классом 'card'
//         var $cardClone = $card.clone(); // Клонировать элемент 'card'
//
//         // Обновить атрибуты и поля в клоне элемента
//         // $cardClone.find('.card-title').text('Ответ ' + ($('.card').length));
//         $cardClone.find('.card-title').text('Новый');
//         $cardClone.find('input[name^="answer"]').each(function () {
//             var originalName = $(this).attr('name');
//             var newName = originalName.replace(/\[(\d+)\]/, function (match, index) {
//                 var newIndex = parseInt(index) + 1;
//                 return '[' + newIndex + ']';
//             });
//             $(this).attr('name', newName);
//             $(this).val(''); // Очистить значение поля в клоне
//         });
//         $cardClone.find('.duplicate-answer').attr("hidden",true);
//         $cardClone.find('.card-footer').append("<button type='button' class='btn btn-danger delete-card'>Удалить</button>")
//         // Вставить клонированный элемент после оригинального элемента 'card'
//         $card.before($cardClone);
//     });
// });

// Обработчик события для кнопки удаления карточки
$(document).on('click', '.delete-card', function () {
    // Находим родительский элемент с классом "card" и удаляем его
    $(this).closest('.card').remove();
});
// Обработчик события для кнопки удаления карточки
$(document).on('click', '.delete-row', function () {
    // Находим родительский элемент с классом "card" и удаляем его
    $(this).parent().parent().remove();
});
