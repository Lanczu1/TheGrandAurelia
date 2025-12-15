<!-- Chat Widget -->
<div id="chat-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 2147483647; display: flex; flex-direction: column; align-items: flex-end; font-family: sans-serif;">

    <!-- Chat Window -->
    <div id="chat-widget" class="hidden bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden mb-4 transition-all duration-300 ease-out transform origin-bottom-right shadow-black/20 opacity-0 translate-y-4"
        style="width: 380px; max-width: 90vw; display: none; flex-direction: column;">

        <!-- Header -->
        <div class="bg-gray-900 p-4 flex justify-between items-center text-white">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <div>
                    <h3 class="font-bold text-sm">Grand Aurelia Concierge</h3>
                    <p class="text-xs text-gray-400">Always here to help</p>
                </div>
            </div>
            <button id="chat-close" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chat-messages" class="h-96 p-4 overflow-y-auto bg-gray-50 flex flex-col gap-2">
            <!-- Messages will be injected here by JS -->
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-gray-100">
            <div class="flex gap-2">
                <input type="text" id="chat-input" placeholder="Type your message..."
                    class="flex-1 px-4 py-2 bg-gray-100 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-all text-gray-800 placeholder-gray-400">
                <button id="chat-send" class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center hover:bg-yellow-600 transition-colors shadow-lg">
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
            <div class="text-center mt-2">
                <p class="text-[10px] text-gray-400">Powered by Grand Aurelia</p>
            </div>
        </div>
    </div>

    <!-- Toggle Button -->
    <button id="chat-toggle" class="group relative w-16 h-16 rounded-full bg-gray-900 text-white flex items-center justify-center shadow-lg hover:bg-yellow-600 transition-all duration-300 hover:scale-110 shadow-black/30 border-2 border-white/10" style="width: 64px; height: 64px; border-radius: 50%;">
        <i class="fas fa-comments text-2xl group-hover:scale-110 transition-transform"></i>
        <!-- Notification Dot -->
        <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 rounded-full border-2 border-white animate-bounce"></span>
    </button>
    <!-- Chat Script -->
    <script src="widget_script.js?v=<?php echo time(); ?>"></script>

    <style>
        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.8) translateY(10px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-pop-in {
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        /* Typing Indicator */
        .typing-dot {
            width: 6px;
            height: 6px;
            background: #9ca3af;
            /* gray-400 */
            border-radius: 50%;
            display: inline-block;
            animation: typing 1.4s infinite ease-in-out both;
            margin: 0 2px;
        }

        .typing-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes typing {

            0%,
            80%,
            100% {
                transform: scale(0);
            }

            40% {
                transform: scale(1);
            }
        }

        /* Mobile Responsive Styles */
        @media (max-width: 640px) {
            #chat-container {
                bottom: 0 !important;
                right: 0 !important;
                left: 0 !important;
                padding: 1rem;
                align-items: flex-end !important;
                width: 100%;
                pointer-events: none;
            }

            #chat-container>* {
                pointer-events: auto;
            }

            #chat-widget {
                width: 100% !important;
                max-width: 100% !important;
                height: 85vh !important;
                border-radius: 1rem 1rem 0 0 !important;
                margin-bottom: 0 !important;
            }

            #chat-messages {
                height: 100% !important;
                flex: 1;
            }

            #chat-toggle {
                margin-bottom: 10px;
            }
        }
    </style>
</div>