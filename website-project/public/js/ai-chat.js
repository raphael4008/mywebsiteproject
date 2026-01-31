import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    createChatWidget();
});

function createChatWidget() {
    const chatWidget = document.createElement('div');
    chatWidget.id = 'ai-chat-widget';
    chatWidget.innerHTML = `
        <div id="chat-header">
            <div class="d-flex align-items-center">
                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                    <i class="fas fa-robot"></i>
                </div>
                <span class="fw-bold">HouseHunter AI</span>
            </div>
            <button id="close-chat" class="btn btn-sm text-white"><i class="fas fa-times"></i></button>
        </div>
        <div id="chat-messages">
            <div class="message bot-message">
                Hello! I'm your AI assistant. I can help you find a home, contact support, or answer questions about our services. How can I help you today?
            </div>
        </div>
        <div id="chat-input-area">
            <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off">
            <button id="send-chat" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i></button>
        </div>
    `;
    
    // Toggle Button
    const toggleBtn = document.createElement('button');
    toggleBtn.id = 'ai-chat-toggle';
    toggleBtn.className = 'btn btn-primary rounded-circle shadow-lg';
    toggleBtn.innerHTML = '<i class="fas fa-comment-dots fa-lg"></i>';
    toggleBtn.title = 'Chat with AI';

    document.body.appendChild(chatWidget);
    document.body.appendChild(toggleBtn);

    // Event Listeners
    toggleBtn.addEventListener('click', () => {
        chatWidget.style.display = 'flex';
        toggleBtn.style.display = 'none';
        document.getElementById('chat-input').focus();
    });

    document.getElementById('close-chat').addEventListener('click', () => {
        chatWidget.style.display = 'none';
        toggleBtn.style.display = 'flex';
    });

    const sendBtn = document.getElementById('send-chat');
    const input = document.getElementById('chat-input');

    const sendMessage = () => {
        const text = input.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        input.value = '';
        
        showTypingIndicator();
        processUserMessage(text);
    };

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
}

function addMessage(text, sender) {
    const messagesContainer = document.getElementById('chat-messages');
    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${sender}-message`;
    msgDiv.textContent = text;
    messagesContainer.appendChild(msgDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showTypingIndicator() {
    const messagesContainer = document.getElementById('chat-messages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'message bot-message text-muted fst-italic';
    typingDiv.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Thinking...';
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function removeTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) indicator.remove();
}

async function processUserMessage(text) {
    try {
        const response = await apiClient.request('/users/ai-chat', 'POST', { message: text });
        removeTypingIndicator();
        if (response.reply) {
            addMessage(response.reply, 'bot');
        } else {
            addMessage("I'm sorry, I'm having trouble connecting right now.", 'bot');
        }
    } catch (error) {
        console.error('AI Chat Error:', error);
        removeTypingIndicator();
        addMessage("I'm sorry, I couldn't get a response. Please try again later.", 'bot');
    }
}