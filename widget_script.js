document.addEventListener('DOMContentLoaded', function () {
    const chatWidget = document.getElementById('chat-widget');
    const chatToggle = document.getElementById('chat-toggle');
    const chatClose = document.getElementById('chat-close');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');

    // Toggle Chat Window
    // Toggle Chat Window
    chatToggle.addEventListener('click', () => {
        const isHidden = chatWidget.style.display === 'none' || chatWidget.style.display === '';

        if (isHidden) {
            // OPEN
            chatWidget.style.display = 'flex';
            chatWidget.classList.remove('hidden');

            // Small delay to allow display:flex to apply before transitioning
            setTimeout(() => {
                chatWidget.classList.remove('opacity-0', 'translate-y-4');
            }, 10);

            // If empty, trigger start with reset
            if (chatMessages.children.length === 0) {
                sendMessage('', 'reset');
            }
        } else {
            // CLOSE
            chatWidget.classList.add('opacity-0', 'translate-y-4');

            // Wait for transition to finish before hiding element
            setTimeout(() => {
                chatWidget.style.display = 'none';
                chatWidget.classList.add('hidden');
            }, 300);
        }
    });

    chatClose.addEventListener('click', () => {
        chatWidget.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => {
            chatWidget.style.display = 'none';
            chatWidget.classList.add('hidden');
        }, 300);
    });

    // Send Message on Click
    chatSend.addEventListener('click', () => {
        const message = chatInput.value.trim();
        if (message) {
            addMessage(message, 'user');
            sendMessage(message);
            chatInput.value = '';
        }
    });

    // Send Message on Enter
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const message = chatInput.value.trim();
            if (message) {
                addMessage(message, 'user');
                sendMessage(message);
                chatInput.value = '';
            }
        }
    });

    function addMessage(text, type) {
        const div = document.createElement('div');
        div.className = `p-3 rounded-lg mb-2 max-w-[80%] break-words text-sm animate-pop-in ${type === 'user'
            ? 'bg-yellow-600 text-white self-end ml-auto'
            : 'bg-gray-100 text-gray-800 self-start mr-auto'
            }`;
        div.innerHTML = text;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addOptions(options) {
        const div = document.createElement('div');
        div.className = 'flex flex-col gap-2 mb-2 self-start mr-auto w-full max-w-[80%] animate-pop-in';

        options.forEach(opt => {
            const btn = document.createElement('button');
            const text = opt.text || opt;
            const value = opt.id || opt;

            btn.className = 'px-4 py-2 bg-white border border-yellow-600 text-yellow-700 text-xs rounded-lg hover:bg-yellow-50 transition-colors text-left w-full';
            btn.textContent = text;
            btn.onclick = () => {
                if (text.toLowerCase() === 'view rooms') {
                    window.location.href = 'view_rooms.php';
                } else {
                    addMessage(text, 'user');
                    sendMessage(value);
                    const parent = btn.parentElement;
                    if (parent) parent.remove();
                }
            };
            div.appendChild(btn);
        });
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function showTypingIndicator() {
        // Prevent duplicate indicators
        if (document.getElementById('typing-indicator')) return;

        const div = document.createElement('div');
        div.id = 'typing-indicator';
        div.className = 'p-3 rounded-lg mb-2 max-w-[80%] bg-gray-100 self-start mr-auto animate-pop-in';
        div.innerHTML = '<div class="flex items-center h-4 px-1"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div>';
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function removeTypingIndicator() {
        const el = document.getElementById('typing-indicator');
        if (el) el.remove();
    }

    function sendMessage(message, action = '') {
        showTypingIndicator();

        fetch('api_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message, action: action })
        })
            .then(res => res.json())
            .then(data => {
                // Artificial delay for realism
                setTimeout(() => {
                    removeTypingIndicator();
                    addMessage(data.message, 'bot');
                    if (data.options) {
                        addOptions(data.options);
                    }
                }, 2000);
            })
            .catch(err => {
                console.error(err);
                removeTypingIndicator();
            });
    }
});
