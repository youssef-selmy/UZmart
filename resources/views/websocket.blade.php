<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Example</title>
    <script src="{{ asset('js/socket.io.js') }}"></script>
</head>
<body>
<h1>WebSocket Example</h1>

<input type="text" id="message-input" placeholder="Enter a message">
<button id="send-button">Send</button>

<ul id="messages-list"></ul>

<script>
    const socket = io('http://localhost:8000');

    socket.on('websocket-channel:App\\Events\\WebSocketEvent', (event) => {
        const message = event.message;
        const listItem = document.createElement('li');
        listItem.textContent = message;
        document.getElementById('messages-list').appendChild(listItem);
    });

    document.getElementById('send-button').addEventListener('click', () => {
        const message = document.getElementById('message-input').value;
        socket.emit('websocket-channel:App\\Events\\WebSocketEvent', { message: message });
    });
</script>
</body>
</html>
