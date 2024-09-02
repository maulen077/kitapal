$(document).ready(function () {
    $(document).on('click', '.duplicate-answer', function () {
        let $card = $(this).closest('.card-primary'); // Найти ближайший элемент с классом 'card'
        let $cardClone = $card.clone(); // Клонировать элемент 'card'
        // Обновить атрибуты и поля в клоне элемента
        // $cardClone.find('.card-title').text('Ответ ' + ($('.card').length));
        $cardClone.find('.card-answer-answer').each(function () {
            let originalName = $(this).attr('name');
            let newName = originalName.replace(/\[(\d+)\]/, function (match, index) {
                let newIndex = parseInt(index) + 1;
                // let newIndex = Math.random();
                return '[' + newIndex + ']';
            });
            $(this).attr('name', newName);
            $(this).val(''); // Очистить значение поля в клоне
            console.log(this);
        });
        $cardClone.find('.card-answer-question').each(function () {
            let originalName = $(this).attr('name');
            let newName = originalName.replace(/\[(\d+)\]/, function (match, index) {
                let newIndex = parseInt(index) + 1;
                // let newIndex = Math.random();
                return '[' + newIndex + ']';
            });
            $(this).attr('name', newName);
            $(this).val(''); // Очистить значение поля в клоне
            console.log(this);
        });
        // Вставить клонированный элемент после оригинального элемента 'card'
        $card.after($cardClone);
    });
});

// Обработчик события для кнопки удаления карточки
$(document).on('click', '.delete-card', function () {
    // Находим родительский элемент с классом "card" и удаляем его
    $(this).closest('.card').remove();
});


const create_modal = document.getElementById('AddNew');
const answerCardFooter = create_modal.getElementsByClassName('answer-card-footer')
const secondCols = create_modal.getElementsByClassName('question-answer-question-col')
const explanation_video_inputs = create_modal.getElementsByClassName('question-file-input')
const check_boxed = create_modal.getElementsByClassName('question-answer-checkbox')
const checkboxes_body = create_modal.getElementsByClassName('form-check')

function hideExtraElementsIfMatch() {
    let create_type_dropdown = document.getElementById('createTypeDropdown')
    is_match = (create_type_dropdown.value === 'match' || create_type_dropdown.value === 'test' || create_type_dropdown.value === 'multi_test')
    for (const secondCol of secondCols) {
        secondCol.hidden = !is_match;
    }
    for (const explanation_video_input of explanation_video_inputs) {
        explanation_video_input.hidden = !is_match;
    }
    for (const checkbox_body of checkboxes_body) {
        checkbox_body.hidden = is_match;
    }
    for (const answerCardFooterElement of answerCardFooter) {
        answerCardFooterElement.hidden = !is_match
    }
    document.getElementById('question-title').hidden = is_match;
}

$('#createTypeDropdown').on('change', () => hideExtraElementsIfMatch());

