{{-- AI Chat Widget - Floating Chat Button with Popup Interface --}}
<div id="ai-chat-widget" class="fixed bottom-8 right-8 z-[9999]">
    {{-- Chat Button --}}
    <button id="chat-toggle-btn" 
            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-full p-4 shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-200 flex items-center justify-center group animate-pulse-slow">
        <svg id="chat-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
        <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse hidden" id="chat-notification">!</span>
    </button>

    {{-- Chat Window --}}
    <div id="chat-window" class="hidden fixed bottom-24 right-8 w-96 bg-white rounded-2xl shadow-2xl overflow-hidden z-[9998]">
        {{-- Chat Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 rounded-lg p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">AI Assistant</h3>
                        <p class="text-xs text-white/80">Powered by Claude AI</p>
                    </div>
                </div>
                <button onclick="toggleChatWindow()" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Chat Suggestions --}}
        <div id="chat-suggestions" class="bg-gray-50 border-b border-gray-200 p-3 overflow-x-auto">
            <div class="flex space-x-2 text-xs">
                {{-- Dynamic suggestions will be loaded here --}}
            </div>
        </div>

        {{-- Chat Messages --}}
        <div id="chat-messages" class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50">
            {{-- Welcome Message --}}
            <div class="flex items-start space-x-3">
                <div class="bg-purple-100 rounded-lg p-2 flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-lg p-3 shadow-sm">
                        <p class="text-sm text-gray-700">Hello! I'm your AI assistant. How can I help you with your projects today?</p>
                        <p class="text-xs text-gray-500 mt-1">Ask me about project status, deadlines, budgets, or any other questions.</p>
                    </div>
                    <span class="text-xs text-gray-400 ml-1 mt-1">Just now</span>
                </div>
            </div>
        </div>

        {{-- Chat Input --}}
        <div class="bg-white border-t border-gray-200 p-4">
            <form id="chat-form" onsubmit="sendChatMessage(event)" class="flex items-end space-x-2">
                <div class="flex-1">
                    <textarea 
                        id="chat-input" 
                        rows="1"
                        maxlength="2000"
                        placeholder="Type your message..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                        onkeydown="handleChatKeydown(event)"
                    ></textarea>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400">
                            <span id="char-count">0</span>/2000
                        </span>
                        <button type="button" onclick="clearChatHistory()" class="text-xs text-gray-400 hover:text-red-500">
                            Clear history
                        </button>
                    </div>
                </div>
                <button type="submit" id="send-btn" class="bg-purple-600 text-white rounded-lg p-2.5 hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Chat JavaScript --}}
@push('scripts')
<script>
    // Chat state
    let chatOpen = false;
    let isProcessing = false;
    let currentContext = null;
    let charCount = 0;

    // Initialize chat
    document.addEventListener('DOMContentLoaded', function() {
        // Set current context
        currentContext = {
            page: window.location.pathname,
            projectId: getProjectIdFromUrl()
        };

        // Load chat history
        loadChatHistory();
        
        // Load suggestions
        loadSuggestions();

        // Setup event listeners
        document.getElementById('chat-toggle-btn').addEventListener('click', toggleChatWindow);
        document.getElementById('chat-input').addEventListener('input', updateCharCount);
        
        // Auto-resize textarea
        const textarea = document.getElementById('chat-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    });

    // Toggle chat window
    function toggleChatWindow() {
        chatOpen = !chatOpen;
        const window = document.getElementById('chat-window');
        const chatIcon = document.getElementById('chat-icon');
        const closeIcon = document.getElementById('close-icon');
        
        if (chatOpen) {
            window.classList.remove('hidden');
            chatIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            document.getElementById('chat-input').focus();
            scrollToBottom();
        } else {
            window.classList.add('hidden');
            chatIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
        }
    }

    // Send chat message
    async function sendChatMessage(event) {
        event.preventDefault();
        
        if (isProcessing) return;
        
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Disable input
        isProcessing = true;
        input.disabled = true;
        document.getElementById('send-btn').disabled = true;
        
        // Add user message to chat
        addMessageToChat('user', message);
        
        // Clear input
        input.value = '';
        input.style.height = 'auto';
        updateCharCount();
        
        // Show typing indicator
        showTypingIndicator();
        
        try {
            // Send to server
            const response = await fetch('{{ route('ai-chat.chat') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: message,
                    context: currentContext.page,
                    project_id: currentContext.projectId
                })
            });
            
            const data = await response.json();
            
            // Remove typing indicator
            removeTypingIndicator();
            
            if (data.success) {
                // Add AI response
                addMessageToChat('ai', data.response);
            } else {
                // Show error
                addMessageToChat('error', data.response || 'An error occurred. Please try again.');
            }
        } catch (error) {
            console.error('Chat error:', error);
            removeTypingIndicator();
            addMessageToChat('error', 'Network error. Please check your connection and try again.');
        } finally {
            // Re-enable input
            isProcessing = false;
            input.disabled = false;
            document.getElementById('send-btn').disabled = false;
            input.focus();
        }
    }

    // Add message to chat
    function addMessageToChat(type, message) {
        const container = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start space-x-3 animate-fade-in';
        
        if (type === 'user') {
            messageDiv.innerHTML = `
                <div class="flex-1 flex justify-end">
                    <div>
                        <div class="bg-purple-600 text-white rounded-lg p-3 shadow-sm max-w-xs">
                            <p class="text-sm">${escapeHtml(message)}</p>
                        </div>
                        <span class="text-xs text-gray-400 text-right block mt-1">${getCurrentTime()}</span>
                    </div>
                </div>
                <div class="bg-gray-100 rounded-lg p-2 flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            `;
        } else if (type === 'ai') {
            messageDiv.innerHTML = `
                <div class="bg-purple-100 rounded-lg p-2 flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-lg p-3 shadow-sm">
                        <div class="text-sm text-gray-700 formatted-content">${formatAIResponse(message)}</div>
                    </div>
                    <span class="text-xs text-gray-400 ml-1 mt-1">${getCurrentTime()}</span>
                </div>
            `;
        } else if (type === 'error') {
            messageDiv.innerHTML = `
                <div class="bg-red-100 rounded-lg p-2 flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="bg-red-50 rounded-lg p-3 shadow-sm">
                        <p class="text-sm text-red-700">${escapeHtml(message)}</p>
                    </div>
                    <span class="text-xs text-gray-400 ml-1 mt-1">${getCurrentTime()}</span>
                </div>
            `;
        }
        
        container.appendChild(messageDiv);
        scrollToBottom();
    }

    // Show typing indicator
    function showTypingIndicator() {
        const container = document.getElementById('chat-messages');
        const indicator = document.createElement('div');
        indicator.id = 'typing-indicator';
        indicator.className = 'flex items-start space-x-3';
        indicator.innerHTML = `
            <div class="bg-purple-100 rounded-lg p-2 flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <div class="bg-white rounded-lg p-3 shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(indicator);
        scrollToBottom();
    }

    // Remove typing indicator
    function removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    // Load chat history
    async function loadChatHistory() {
        try {
            const response = await fetch('{{ route('ai-chat.history') }}');
            const data = await response.json();
            
            if (data.success && data.history.length > 0) {
                const container = document.getElementById('chat-messages');
                // Clear welcome message
                container.innerHTML = '';
                
                // Add history messages
                data.history.forEach(item => {
                    addMessageToChat('user', item.message);
                    addMessageToChat('ai', item.response);
                });
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    }

    // Load suggestions
    async function loadSuggestions() {
        try {
            const response = await fetch('{{ route('ai-chat.suggestions') }}?' + new URLSearchParams({
                context: getContextFromPath(),
                project_id: currentContext.projectId || ''
            }));
            const data = await response.json();
            
            if (data.success && data.suggestions.length > 0) {
                const container = document.getElementById('chat-suggestions');
                container.innerHTML = data.suggestions.map(suggestion => `
                    <button onclick="useSuggestion('${escapeHtml(suggestion)}')" 
                            class="bg-white border border-gray-200 rounded-full px-3 py-1 hover:bg-purple-50 hover:border-purple-300 transition-colors whitespace-nowrap">
                        ${escapeHtml(suggestion)}
                    </button>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading suggestions:', error);
        }
    }

    // Use suggestion
    function useSuggestion(text) {
        const input = document.getElementById('chat-input');
        input.value = text;
        input.focus();
        updateCharCount();
    }

    // Clear chat history
    async function clearChatHistory() {
        if (!confirm('Are you sure you want to clear the chat history?')) return;
        
        try {
            const response = await fetch('{{ route('ai-chat.clear-history') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            if (data.success) {
                // Clear messages and show welcome
                const container = document.getElementById('chat-messages');
                container.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <div class="bg-purple-100 rounded-lg p-2 flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-sm text-gray-700">Chat history cleared. How can I help you today?</p>
                            </div>
                            <span class="text-xs text-gray-400 ml-1 mt-1">Just now</span>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error clearing chat history:', error);
        }
    }

    // Handle Enter key in textarea
    function handleChatKeydown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            document.getElementById('chat-form').requestSubmit();
        }
    }

    // Update character count
    function updateCharCount() {
        const input = document.getElementById('chat-input');
        const count = input.value.length;
        document.getElementById('char-count').textContent = count;
        
        if (count >= 1900) {
            document.getElementById('char-count').parentElement.classList.add('text-red-500');
        } else {
            document.getElementById('char-count').parentElement.classList.remove('text-red-500');
        }
    }

    // Helper functions
    function scrollToBottom() {
        const container = document.getElementById('chat-messages');
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 100);
    }

    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatAIResponse(text) {
        // Convert markdown-like formatting
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n\n/g, '</p><p class="mt-2">')
            .replace(/\n/g, '<br>')
            .replace(/^/, '<p>')
            .replace(/$/, '</p>')
            .replace(/- (.*?)(?=<br>|<\/p>)/g, '<li class="ml-4">$1</li>')
            .replace(/<li/g, '<ul class="list-disc ml-4 mt-1"><li')
            .replace(/<\/li>(?!.*<li)/g, '</li></ul>');
    }

    function getProjectIdFromUrl() {
        const match = window.location.pathname.match(/\/projects\/(\d+)/);
        return match ? match[1] : null;
    }

    function getContextFromPath() {
        const path = window.location.pathname;
        if (path.includes('/dashboard')) return 'dashboard';
        if (path.includes('/projects')) return 'project';
        if (path.includes('/invoices')) return 'invoices';
        if (path.includes('/project-intelligence')) return 'intelligence';
        return 'general';
    }
</script>

{{-- Animation Styles --}}
<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse-slow {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.9;
            transform: scale(1.05);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
    
    .animate-pulse-slow {
        animation: pulse-slow 3s ease-in-out infinite;
    }
    
    #chat-toggle-btn:hover {
        animation: none;
    }

    .formatted-content p {
        margin-top: 0.5rem;
    }

    .formatted-content p:first-child {
        margin-top: 0;
    }

    .formatted-content ul {
        list-style-type: disc;
        margin-left: 1rem;
        margin-top: 0.25rem;
    }

    .formatted-content strong {
        font-weight: 600;
        color: #374151;
    }

    /* Custom scrollbar for chat messages */
    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    #chat-messages::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 3px;
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: #9ca3af;
        border-radius: 3px;
    }

    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }
</style>
@endpush