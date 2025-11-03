{{-- AI Chat Widget with Theme Styling --}}
<div style="position: fixed; bottom: 20px; right: 20px; z-index: 999999;">
    {{-- Chat Button - Always Visible --}}
    <button onclick="toggleAIChat()" 
            id="ai-chat-button"
            style="background: var(--theme-primary);
                   color: white;
                   border: none;
                   border-radius: 50%;
                   width: 60px;
                   height: 60px;
                   display: flex;
                   align-items: center;
                   justify-content: center;
                   box-shadow: var(--theme-shadow-lg);
                   cursor: pointer;
                   transition: all 0.3s ease;">
        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
            </path>
        </svg>
    </button>
    
    {{-- Chat Window - Larger Size --}}
    <div id="ai-chat-window" 
         style="display: none;
                position: fixed;
                bottom: 90px;
                right: 20px;
                width: 450px;
                height: 600px;
                background: var(--theme-bg-primary, white);
                border-radius: var(--theme-border-radius-lg, 12px);
                box-shadow: var(--theme-shadow-xl);
                border: 1px solid var(--theme-border, #e2e8f0);
                z-index: 999998;
                overflow: hidden;">
        
        {{-- Header --}}
        <div style="background: var(--theme-primary);
                    color: white;
                    padding: 16px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21.928 11.607c-.202-.488-.635-.605-.928-.633V8c0-1.103-.897-2-2-2h-6V4.61c.305-.274.5-.668.5-1.11a1.5 1.5 0 0 0-3 0c0 .442.195.836.5 1.11V6H5c-1.103 0-2 .897-2 2v2.997l-.082.006A1 1 0 0 0 1.99 12v2a1 1 0 0 0 1 1H3v5c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-5a1 1 0 0 0 1-1v-1.938a1.006 1.006 0 0 0-.072-.455zM5 20V8h14l.001 3.996L19 12v2l.001.005.001 5.995H5z"/>
                    <ellipse cx="8.5" cy="12" rx="1.5" ry="2"/>
                    <ellipse cx="15.5" cy="12" rx="1.5" ry="2"/>
                    <path d="M8 16h8v2H8z"/>
                </svg>
                <h3 style="margin: 0; font-size: var(--theme-font-size-lg, 16px); font-weight: 600;">AI Assistant</h3>
            </div>
            <button onclick="toggleAIChat()" 
                    style="background: none; border: none; color: white; cursor: pointer; padding: 4px;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        {{-- Messages Area - Larger with animated background --}}
        <div id="chat-messages" 
             style="padding: 20px; 
                    height: calc(100% - 140px); 
                    overflow-y: auto; 
                    background: var(--theme-bg-secondary, #f9fafb);
                    font-family: var(--theme-font-family);
                    
                    position: relative;">
            {{-- Animated Network Background --}}
            <canvas id="chat-background-canvas" 
                    style="position: absolute; 
                           top: 0; 
                           left: 0; 
                           width: 100%; 
                           height: 100%; 
                           pointer-events: none;
                           opacity: 0.3;"></canvas>
            <div style="position: relative; z-index: 1;">
                @php
                    $aiSettings = \App\Models\AiSetting::current();
                    $welcomeMessage = $aiSettings->ai_chat_welcome_message ?: "Hello! I'm your AI assistant. How can I help you today?";
                @endphp
                <div style="background: white; 
                        padding: 14px 16px; 
                        border-radius: var(--theme-border-radius, 8px); 
                        margin-bottom: 12px;
                        box-shadow: var(--theme-shadow-sm);
                        border: 1px solid var(--theme-border, #e5e7eb);">
                <div style="display: flex; align-items: start; gap: 10px;">
                    <div style="width: 32px; height: 32px; 
                                background: var(--theme-primary); 
                                color: white;
                                border-radius: 50%; 
                                display: flex; 
                                align-items: center; 
                                justify-content: center;
                                flex-shrink: 0;">
                        <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21.928 11.607c-.202-.488-.635-.605-.928-.633V8c0-1.103-.897-2-2-2h-6V4.61c.305-.274.5-.668.5-1.11a1.5 1.5 0 0 0-3 0c0 .442.195.836.5 1.11V6H5c-1.103 0-2 .897-2 2v2.997l-.082.006A1 1 0 0 0 1.99 12v2a1 1 0 0 0 1 1H3v5c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-5a1 1 0 0 0 1-1v-1.938a1.006 1.006 0 0 0-.072-.455zM5 20V8h14l.001 3.996L19 12v2l.001.005.001 5.995H5z"/>
                        </svg>
                    </div>
                    <p style="margin: 0; color: var(--theme-text); line-height: 1.5;">{{ $welcomeMessage }}</p>
                </div>
                </div>
            </div>
        </div>
        
        {{-- Input Area --}}
        <form id="chat-form" onsubmit="sendMessage(event)" 
              style="padding: 16px 20px; 
                     border-top: 1px solid var(--theme-border, #e2e8f0); 
                     background: var(--theme-bg-primary, white);">
            <div style="display: flex; gap: 10px;">
                <input type="text" 
                       id="chat-input"
                       placeholder="Type your message..." 
                       style="flex: 1;
                              padding: 10px 14px;
                              border: 1px solid var(--theme-border, #cbd5e0);
                              border-radius: var(--theme-border-radius, 8px);
                              
                              font-family: var(--theme-font-family);
                              color: var(--theme-text);
                              background: var(--theme-bg-primary);
                              outline: none;
                              transition: border-color 0.2s;">
                <button type="submit"
                        style="background: var(--theme-primary);
                               color: white;
                               border: none;
                               padding: 10px 20px;
                               border-radius: var(--theme-border-radius, 8px);
                               cursor: pointer;
                               
                               font-weight: 500;
                               transition: opacity 0.2s;
                               display: flex;
                               align-items: center;
                               gap: 6px;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Animated glow effect for chat button */
    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 0 20px rgba(var(--theme-primary-rgb), 0.5),
                        0 0 40px rgba(var(--theme-primary-rgb), 0.3),
                        var(--theme-shadow-lg);
        }
        50% {
            box-shadow: 0 0 30px rgba(var(--theme-primary-rgb), 0.7),
                        0 0 60px rgba(var(--theme-primary-rgb), 0.4),
                        var(--theme-shadow-xl);
        }
    }
    
    #ai-chat-button {
        animation: pulse-glow 3s ease-in-out infinite;
    }
    
    /* Hover effects */
    #ai-chat-button:hover {
        transform: scale(1.05);
        animation: none;
        box-shadow: 0 0 40px rgba(var(--theme-primary-rgb), 0.8),
                    var(--theme-shadow-xl) !important;
    }
    
    #chat-input:focus {
        border-color: var(--theme-primary) !important;
        box-shadow: 0 0 0 3px rgba(var(--theme-primary-rgb), 0.1) !important;
    }
    
    #chat-form button[type="submit"]:hover {
        opacity: 0.9;
    }
    
    /* Scrollbar styling */
    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    #chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    
    #chat-messages::-webkit-scrollbar-thumb {
        background: var(--theme-border, #cbd5e0);
        border-radius: 3px;
    }
    
    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: var(--theme-text-muted, #9ca3af);
    }
    
    /* Message animation */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .chat-message {
        animation: slideIn 0.3s ease-out;
    }
</style>

<script>
function toggleAIChat() {
    const chatWindow = document.getElementById('ai-chat-window');
    if (chatWindow) {
        chatWindow.style.display = chatWindow.style.display === 'none' ? 'block' : 'none';
        // Focus input when opening
        if (chatWindow.style.display === 'block') {
            setTimeout(() => {
                document.getElementById('chat-input').focus();
            }, 100);
        }
    }
}

function sendMessage(event) {
    event.preventDefault();
    const input = document.getElementById('chat-input');
    const messagesDiv = document.getElementById('chat-messages');
    
    if (!input.value.trim()) return;
    
    // Add user message with theme styling
    const userMsg = document.createElement('div');
    userMsg.className = 'chat-message';
    userMsg.style.cssText = `
        background: var(--theme-primary);
        color: white;
        padding: 14px 16px;
        border-radius: var(--theme-border-radius, 8px);
        margin-bottom: 12px;
        margin-left: 60px;
        box-shadow: var(--theme-shadow-sm);
        
        line-height: 1.5;
        position: relative;
        z-index: 1;
    `;
    
    const userMsgContent = `
        <div style="display: flex; align-items: start; gap: 10px; justify-content: flex-end;">
            <p style="margin: 0;">${escapeHtml(input.value)}</p>
            <div style="width: 32px; height: 32px; 
                        background: rgba(255, 255, 255, 0.2); 
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center;
                        flex-shrink: 0;">
                <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                </svg>
            </div>
        </div>
    `;
    userMsg.innerHTML = userMsgContent;
    messagesDiv.appendChild(userMsg);
    
    // Save message
    const message = input.value;
    input.value = '';
    
    // Add loading message with theme styling
    const loadingMsg = document.createElement('div');
    loadingMsg.className = 'chat-message';
    loadingMsg.style.cssText = `
        background: white;
        padding: 14px 16px;
        border-radius: var(--theme-border-radius, 8px);
        margin-bottom: 12px;
        margin-right: 60px;
        box-shadow: var(--theme-shadow-sm);
        border: 1px solid var(--theme-border, #e5e7eb);
        position: relative;
        z-index: 1;
    `;
    loadingMsg.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; 
                        background: var(--theme-primary); 
                        color: white;
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center;
                        flex-shrink: 0;">
                <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21.928 11.607c-.202-.488-.635-.605-.928-.633V8c0-1.103-.897-2-2-2h-6V4.61c.305-.274.5-.668.5-1.11a1.5 1.5 0 0 0-3 0c0 .442.195.836.5 1.11V6H5c-1.103 0-2 .897-2 2v2.997l-.082.006A1 1 0 0 0 1.99 12v2a1 1 0 0 0 1 1H3v5c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-5a1 1 0 0 0 1-1v-1.938a1.006 1.006 0 0 0-.072-.455zM5 20V8h14l.001 3.996L19 12v2l.001.005.001 5.995H5z"/>
                </svg>
            </div>
            <div style="display: flex; gap: 4px; align-items: center; color: var(--theme-text-muted);">
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    `;
    messagesDiv.appendChild(loadingMsg);
    
    // Scroll to bottom
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    
    // Send to server
    fetch('{{ route("ai-chat.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            message: message,
            context: window.location.pathname
        })
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading message
        messagesDiv.removeChild(loadingMsg);
        
        // Add AI response with theme styling
        const aiMsg = document.createElement('div');
        aiMsg.className = 'chat-message';
        aiMsg.style.cssText = `
            background: white;
            padding: 14px 16px;
            border-radius: var(--theme-border-radius, 8px);
            margin-bottom: 12px;
            margin-right: 60px;
            box-shadow: var(--theme-shadow-sm);
            border: 1px solid var(--theme-border, #e5e7eb);
            
            line-height: 1.5;
        `;
        aiMsg.innerHTML = `
            <div style="display: flex; align-items: start; gap: 10px;">
                <div style="width: 32px; height: 32px; 
                            background: var(--theme-primary); 
                            color: white;
                            border-radius: 50%; 
                            display: flex; 
                            align-items: center; 
                            justify-content: center;
                            flex-shrink: 0;">
                    <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21.928 11.607c-.202-.488-.635-.605-.928-.633V8c0-1.103-.897-2-2-2h-6V4.61c.305-.274.5-.668.5-1.11a1.5 1.5 0 0 0-3 0c0 .442.195.836.5 1.11V6H5c-1.103 0-2 .897-2 2v2.997l-.082.006A1 1 0 0 0 1.99 12v2a1 1 0 0 0 1 1H3v5c0 1.103.897 2 2 2h14c1.103 0 2-.897 2-2v-5a1 1 0 0 0 1-1v-1.938a1.006 1.006 0 0 0-.072-.455zM5 20V8h14l.001 3.996L19 12v2l.001.005.001 5.995H5z"/>
                    </svg>
                </div>
                <p style="margin: 0; color: var(--theme-text);">${data.response || 'Sorry, I could not process your request.'}</p>
            </div>
        `;
        messagesDiv.appendChild(aiMsg);
        
        // Scroll to bottom
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    })
    .catch(error => {
        console.error('Error:', error);
        messagesDiv.removeChild(loadingMsg);
        
        const errorMsg = document.createElement('div');
        errorMsg.className = 'chat-message';
        errorMsg.style.cssText = `
            background: rgba(var(--theme-danger-rgb), 0.05);
            padding: 14px 16px;
            border-radius: var(--theme-border-radius, 8px);
            margin-bottom: 12px;
            margin-right: 60px;
            border: 1px solid rgba(var(--theme-danger-rgb), 0.3);
        `;
        errorMsg.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg style="width: 20px; height: 20px; color: var(--theme-danger); flex-shrink: 0;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <p style="margin: 0; color: var(--theme-danger);">Error: Could not connect to AI service.</p>
            </div>
        `;
        messagesDiv.appendChild(errorMsg);
    });
}

// Escape HTML function for security
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
// Initialize animated background
initChatBackground();

function initChatBackground() {
    const canvas = document.getElementById('chat-background-canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    let particles = [];
    let animationId;
    
    // Set canvas size
    function resizeCanvas() {
        const container = canvas.parentElement;
        canvas.width = container.offsetWidth;
        canvas.height = container.offsetHeight;
    }
    
    resizeCanvas();
    
    // Particle class
    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.vx = (Math.random() - 0.5) * 0.5;
            this.vy = (Math.random() - 0.5) * 0.5;
            this.radius = Math.random() * 2 + 1;
            this.opacity = Math.random() * 0.5 + 0.2;
        }
        
        update() {
            this.x += this.vx;
            this.y += this.vy;
            
            // Bounce off walls
            if (this.x < 0 || this.x > canvas.width) this.vx = -this.vx;
            if (this.y < 0 || this.y > canvas.height) this.vy = -this.vy;
            
            // Keep particles in bounds
            this.x = Math.max(0, Math.min(canvas.width, this.x));
            this.y = Math.max(0, Math.min(canvas.height, this.y));
        }
        
        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            
            // Use theme primary color
            const primaryColor = getComputedStyle(document.documentElement)
                .getPropertyValue('--theme-primary') || '#667eea';
            
            ctx.fillStyle = primaryColor + Math.floor(this.opacity * 255).toString(16).padStart(2, '0');
            ctx.fill();
        }
    }
    
    // Create particles
    function createParticles() {
        particles = [];
        const particleCount = Math.min(20, Math.floor(canvas.width * canvas.height / 10000));
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }
    }
    
    createParticles();
    
    // Draw connections between nearby particles
    function drawConnections() {
        const primaryColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--theme-primary') || '#667eea';
        
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < 100) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    
                    const opacity = (1 - distance / 100) * 0.2;
                    ctx.strokeStyle = primaryColor + Math.floor(opacity * 255).toString(16).padStart(2, '0');
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }
    }
    
    // Animation loop
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Update and draw particles
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        // Draw connections
        drawConnections();
        
        animationId = requestAnimationFrame(animate);
    }
    
    // Start animation
    animate();
    
    // Handle resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            resizeCanvas();
            createParticles();
        }, 250);
    });
    
    // Clean up when chat is closed
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.id === 'ai-chat-window' && 
                mutation.attributeName === 'style') {
                const display = window.getComputedStyle(mutation.target).display;
                if (display === 'none') {
                    cancelAnimationFrame(animationId);
                } else if (display === 'block') {
                    resizeCanvas();
                    createParticles();
                    animate();
                }
            }
        });
    });
    
    const chatWindow = document.getElementById('ai-chat-window');
    if (chatWindow) {
        observer.observe(chatWindow, { attributes: true, attributeFilter: ['style'] });
    }
}
</script>

<style>
    /* Typing indicator animation */
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 3px;
    }
    
    .typing-indicator span {
        height: 8px;
        width: 8px;
        background-color: var(--theme-text-muted);
        border-radius: 50%;
        display: inline-block;
        animation: typing 1.4s infinite ease-in-out;
    }
    
    .typing-indicator span:nth-child(1) {
        animation-delay: -0.32s;
    }
    
    .typing-indicator span:nth-child(2) {
        animation-delay: -0.16s;
    }
    
    @keyframes typing {
        0%, 80%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        40% {
            transform: scale(1.3);
            opacity: 0.7;
        }
    }
</style>