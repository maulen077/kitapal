// Get the URL query string
const queryString = window.location.search;

// Create a URLSearchParams object from the query string
const params = new URLSearchParams(queryString);

// Get the value of the 'user_id' parameter
const employee_id = $('#employee_id').val();

socket = new WebSocket("wss://altynbelgi.1-000.kz:1995?employee_id=" + employee_id);
socket.onopen = (event) => {
};

socket.onmessage = (event) => {
    data = JSON.parse(event.data)
    console.log(data);
    if(data['type'] === 'message') {
        date = Date.parse(data['created_at']);
        date = moment(date).add(-6, 'hours').format('YYYY-MM-DD HH:mm:ss')
        if(data['user_id'] === employee_id) {

        }
        message_html = data['user_id'] ? `
        <!-- Message. Default to the left -->
        <div class="direct-chat-msg">
            <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-left">${data['name']}</span>
                <span class="direct-chat-timestamp float-right">${date}</span>
            </div>
            <!-- /.direct-chat-infos -->
            <img class="direct-chat-img" src="${(data['avatar'] !== null && data['avatar'] !== '') ? data['avatar'] : 'https://altynbelgi.1-000.kz/placeholders/avatar.jpg'}" alt="message user image">
            <!-- /.direct-chat-img -->
            <div class="direct-chat-text">
                ${data['text']}
            </div>
            <!-- /.direct-chat-text -->
        </div>
        `
            :
            `
        <div class="direct-chat-msg right">
            <div class="right">
                <div class="direct-chat-infos clearfix">
                    <span class="direct-chat-name float-right">${data['name']}</span>
                    <span class="direct-chat-timestamp float-left">${date}</span>
                </div>
                <!-- /.direct-chat-infos -->
                <img class="direct-chat-img" src="${(data['avatar'] !== null && data['avatar'] !== '') ? data['avatar'] : 'https://altynbelgi.1-000.kz/placeholders/avatar.jpg'}" alt="message user image">
                <!-- /.direct-chat-img -->
                <div class="direct-chat-text">
                    ${data['text']}
                </div>
            </div>
            <!-- /.direct-chat-text -->
        </div>
`
        $('#direct-chat-messages').append(message_html);

    }
};

function send_message(chat_id) {
    input = document.getElementById('chat_input')
    input_message = input.value
    if(input_message !== "" && input_message !== null) {
        input.value = '';
        message = {
            "action":"message",
            "text":input_message,
            "chat_id":chat_id
        }
        socket.send(JSON.stringify(message));
    }
}
