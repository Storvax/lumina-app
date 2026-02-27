<script>
    // --- Configuração e Estado Inicial ---
    const currentUserId = {{ Auth::id() ?? 'null' }};
    const roomId = {{ $room->id }};
    const isModerator = {{ Auth::user()->isModerator() ? 'true' : 'false' }};
    const followingIds = @json($followingIds ?? []); 
    
    let isSensitive = false;
    let isDnd = false;
    let isCrisisMode = {{ $room->is_crisis_mode ? 'true' : 'false' }};
    let typingTimer;
    let messageState = 'new';
    let targetMessageId = null;

    // Smart Scroll Variables
    let isUserAtBottom = true;
    let unreadMessagesCount = 0;
    const scrollThreshold = 150; // Tolerância de pixeis para considerar "no fundo"

    // --- DOM Loaded ---
    document.addEventListener('DOMContentLoaded', () => {
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('messageInput');
        const cwBtn = document.getElementById('cw-btn');
        const container = document.getElementById('chat-container');
        
        // Scroll Inicial
        scrollToBottom(true);
        updateCrisisUI(isCrisisMode);
        
        // Leitura Automática
        markMessagesAsRead();
        window.addEventListener('focus', markMessagesAsRead);

        // Smart Scroll Listener
        if(container) {
            container.addEventListener('scroll', () => {
                const distanceToBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
                isUserAtBottom = distanceToBottom < scrollThreshold;
                
                if (isUserAtBottom) {
                    unreadMessagesCount = 0;
                    updateUnreadBadge();
                }
            });
        }

        // Toggle Conteúdo Sensível
        if(cwBtn) {
            cwBtn.addEventListener('click', () => {
                isSensitive = !isSensitive;
                if(isSensitive) {
                    cwBtn.classList.replace('text-slate-400', 'text-rose-500'); cwBtn.classList.add('bg-rose-50');
                    messageInput.placeholder = "⚠️ Conteúdo Sensível..."; messageInput.parentElement.parentElement.classList.add('ring-2', 'ring-rose-200');
                } else {
                    cwBtn.classList.replace('text-rose-500', 'text-slate-400'); cwBtn.classList.remove('bg-rose-50');
                    messageInput.placeholder = "Escreve a tua mensagem..."; messageInput.parentElement.parentElement.classList.remove('ring-2', 'ring-rose-200');
                }
            });
        }

        // Eventos do Input
        if(chatForm) {
            chatForm.addEventListener('submit', async (e) => { e.preventDefault(); await handleMessageSubmit(); });
            
            messageInput.addEventListener('keydown', (e) => {
                if(e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleMessageSubmit(); }
                if(e.key === 'Escape') cancelReplyOrEdit();
            });
            
            messageInput.addEventListener('input', () => {
                resizeTextarea(messageInput);
                if(window.Echo && !isDnd) window.Echo.join(`chat.${roomId}`).whisper('typing', { name: "{{ Auth::user()->name }}" });
            });
        }

        // Iniciar WebSockets
        const waitForEcho = setInterval(() => { if (window.Echo) { clearInterval(waitForEcho); initChatSystem(); } }, 100);
    });

    // --- WebSockets (Reverb) ---
    function initChatSystem() {
        window.Echo.join(`chat.${roomId}`)
            .here((users) => { updateCounters(users.length); users.forEach(addUserToSidebar); })
            .joining((user) => { addUserToSidebar(user); updateCounters(1, true); if(followingIds.includes(user.id)) showToast(`👋 ${user.name} entrou!`, true); })
            .leaving((user) => { removeUserFromSidebar(user); updateCounters(-1, true); })
            
            .listen('MessageSent', (e) => {
                if(isDnd) return;
                
                // Verificação de Segurança (Evita o erro 'undefined')
                if (e.message && e.message.user_id !== currentUserId) {
                    appendMessage(e.message);
                    
                    const ind = document.getElementById('typing-indicator');
                    if(ind) ind.classList.add('opacity-0');
                    
                    markMessagesAsRead();
                    
                    // Smart Scroll Logic: Se o user não estiver no fundo, não faz scroll
                    scrollToBottom(false);
                } else {
                    // Se fui eu a enviar noutro tab/device, força scroll
                    scrollToBottom(true);
                }
            })
            
            .listen('MessageUpdated', (e) => { updateMessageInDOM(e.message); })
            
            .listen('MessageDeleted', (e) => {
                const el = document.getElementById(`msg-${e.messageId}`);
                if(el) { el.classList.add('opacity-0', 'scale-95'); setTimeout(() => el.remove(), 300); }
            })
            
            .listen('MessageReacted', (e) => {
                if(isDnd) return;
                updateReactionUI(e.message_id, e.type, e.count);
                if (e.message_owner_id === currentUserId && e.action === 'added') triggerSupportEffect(e.type);
            })
            
            .listen('MessageRead', (e) => {
                if(e.userId !== currentUserId) {
                    e.messageIds.forEach(id => {
                        const check = document.querySelector(`#msg-${id} .read-check i`);
                        if(check) check.className = 'ri-check-double-line text-blue-500';
                    });
                }
            })
            
            .listen('RoomStatusUpdated', (e) => { updateCrisisUI(e.status === 'crisis'); })
            
            .listenForWhisper('typing', (e) => { if(!isDnd) showTypingIndicator(e.name); });
    }

    // --- Lógica de Scroll Inteligente ---
    function scrollToBottom(force = false) {
        const container = document.getElementById('chat-container');
        if (!container) return;

        if (isUserAtBottom || force) {
            container.scrollTop = container.scrollHeight;
            isUserAtBottom = true;
            unreadMessagesCount = 0;
            updateUnreadBadge();
        } else {
            if(!force) {
                unreadMessagesCount++;
                updateUnreadBadge();
            }
        }
    }

    function updateUnreadBadge() {
        const btn = document.getElementById('jump-to-bottom-btn');
        const badge = document.getElementById('unread-count-badge');
        if(!btn || !badge) return;

        if (unreadMessagesCount > 0) {
            btn.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            badge.textContent = unreadMessagesCount;
            badge.classList.remove('hidden');
        } else {
            btn.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            badge.classList.add('hidden');
        }
    }

    // --- Toggle Modo Compacto/Confortável ---
    window.toggleViewMode = async function() {
        const container = document.getElementById('chat-container');
        const btn = document.getElementById('view-mode-btn');
        const btnIcon = btn.querySelector('i');

        // Optimistic UI
        const isCompact = container.classList.contains('compact-mode');
        
        if (isCompact) {
            // Mudar para Confortável
            container.classList.remove('compact-mode', 'space-y-2', 'p-2');
            container.classList.add('space-y-6');
            btnIcon.className = 'ri-list-unordered';
            btn.classList.replace('bg-indigo-50', 'bg-white');
            btn.classList.replace('text-indigo-600', 'text-slate-400');
            btn.classList.replace('border-indigo-200', 'border-slate-200');
        } else {
            // Mudar para Compacto
            container.classList.add('compact-mode', 'space-y-2', 'p-2');
            container.classList.remove('space-y-6');
            btnIcon.className = 'ri-list-check-2';
            btn.classList.replace('bg-white', 'bg-indigo-50');
            btn.classList.replace('text-slate-400', 'text-indigo-600');
            btn.classList.replace('border-slate-200', 'border-indigo-200');
        }

        // Persistir no Backend
        try { await axios.post('/chat/preferences/mode'); } 
        catch(e) { console.error("Erro ao guardar preferência.", e); }
    };

    // --- Envio de Mensagens ---
    async function handleMessageSubmit() {
        const input = document.getElementById('messageInput');
        const content = input.value.trim();
        if (!content) return;

        input.value = ''; resizeTextarea(input); input.focus();
        const currentState = messageState;
        const currentTarget = targetMessageId;
        cancelReplyOrEdit(); 

        try {
            if (currentState === 'edit') {
                await axios.patch(`/chat/${roomId}/message/${currentTarget}`, { content });
                const msgContent = document.querySelector(`#msg-${currentTarget} .message-text`);
                if(msgContent) msgContent.innerText = content;
            } else {
                const payload = { 
                    content, 
                    is_sensitive: isSensitive,
                    is_anonymous: document.getElementById('anonymous-toggle')?.checked || false,
                    reply_to_id: currentState === 'reply' ? currentTarget : null
                };
                const response = await axios.post(`/chat/${roomId}/message`, payload);
                if (response.data.status === 'Message Sent!') { 
                    // Se fui eu, append e força scroll
                    appendMessage(response.data.message); 
                    scrollToBottom(true); 
                }
                if(response.data.crisis_detected) document.getElementById('crisis-banner').classList.remove('hidden');
            }
        } catch (error) {
            console.error(error);
            alert(error.response?.data?.error || "Erro ao processar mensagem.");
            input.value = content;
        }
    }

    // --- Reply & Edit UI ---
    window.startReply = function(id, name) {
        // Busca texto do DOM para evitar erros de sintaxe JS com aspas
        const el = document.querySelector(`#msg-${id} .message-text`);
        const text = el ? el.innerText.substring(0, 30) + '...' : '...';
        
        messageState = 'reply'; targetMessageId = id;
        showInputBar(`A responder a ${name}`, text, 'ri-reply-fill text-indigo-500');
    };

    window.startEdit = function(id) {
        const el = document.querySelector(`#msg-${id} .message-text`);
        const text = el ? el.innerText : '';
        
        messageState = 'edit'; targetMessageId = id;
        document.getElementById('messageInput').value = text;
        showInputBar(`A editar mensagem`, null, 'ri-pencil-fill text-amber-600', 'amber');
    };

    function showInputBar(title, subtitle, iconClass, color = 'slate') {
        const container = document.getElementById('chat-form').parentElement;
        let bar = document.getElementById('action-bar');
        if(!bar) {
            bar = document.createElement('div'); bar.id = 'action-bar';
            container.insertBefore(bar, document.getElementById('chat-form'));
        }
        const bgColor = color === 'amber' ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200';
        bar.className = `flex items-center justify-between ${bgColor} border-t border-l border-r rounded-t-xl px-4 py-2 mb-[-5px] mx-2 relative z-0 text-xs transition-all`;
        bar.innerHTML = `<div class="flex items-center gap-2 border-l-2 ${color === 'amber' ? 'border-amber-500' : 'border-indigo-500'} pl-2"><i class="${escapeHtml(iconClass)}"></i><div><span class="font-bold ${color === 'amber' ? 'text-amber-700' : 'text-indigo-600'}">${escapeHtml(title)}</span>${subtitle ? `<p class="text-slate-500 truncate max-w-[200px]">${escapeHtml(subtitle)}</p>` : ''}</div></div><button onclick="cancelReplyOrEdit()" class="text-slate-400 hover:text-rose-500"><i class="ri-close-circle-fill text-lg"></i></button>`;
        document.getElementById('messageInput').focus();
    }

    window.cancelReplyOrEdit = function() {
        messageState = 'new'; targetMessageId = null;
        const bar = document.getElementById('action-bar');
        if(bar) bar.remove();
        document.getElementById('messageInput').value = '';
    };

    // --- Escape HTML para prevenir XSS ---
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = String(text ?? '');
        return div.innerHTML;
    }

    // --- Geração de HTML Dinâmico (Append) ---
    function appendMessage(data) {
        const isMe = data.user_id === currentUserId;
        const div = document.createElement('div');
        div.id = `msg-${data.id}`;
        // Adiciona classe 'message-wrapper' para suporte ao modo compacto
        div.className = `message-wrapper flex w-full ${isMe ? 'justify-end' : 'justify-start'} animate-fade-up group mb-4 relative`;

        let replyHtml = '';
        if (data.reply_to) {
            const replyName = data.reply_to.user_id === currentUserId ? 'Ti' : escapeHtml(data.reply_to.user?.name || 'Alguém');
            const replyContent = escapeHtml(data.reply_to.content);
            replyHtml = `<div class="mb-1 text-xs border-l-2 ${isMe ? 'border-indigo-300 bg-indigo-700/30 text-indigo-100' : 'border-indigo-500 bg-slate-100 text-slate-500'} pl-2 py-1 rounded-r opacity-80 cursor-pointer" onclick="document.getElementById('msg-${data.reply_to_id}').scrollIntoView({behavior: 'smooth', block: 'center'})"><span class="font-bold block text-[10px]">${replyName}</span><span class="truncate block max-w-[150px]">${replyContent}</span></div>`;
        }

        const senderName = isMe ? 'ti mesmo' : (data.is_anonymous ? 'Anónimo' : escapeHtml(data.user?.name || 'Alguém'));
        let menuHtml = `<button onclick="startReply(${data.id}, ${JSON.stringify(senderName)})" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2"><i class="ri-reply-line"></i> Responder</button>`;
        
        if (isModerator || isMe) {
            menuHtml += `<button onclick="startEdit(${data.id})" class="w-full text-left px-3 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50 flex items-center gap-2"><i class="ri-pencil-line"></i> Editar</button>`;
            menuHtml += `<form onsubmit="deleteMessage(event, ${data.id})" class="block"><button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2"><i class="ri-delete-bin-line"></i> Apagar</button></form>`;
        }
        if (!isMe) {
            menuHtml += `<button onclick="reportMessage(${data.id})" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2"><i class="ri-flag-line"></i> Denunciar</button>`;
            if(isModerator) menuHtml += `<div class="h-px bg-slate-100 my-1"></div><button onclick="muteUser(${data.user_id})" class="w-full text-left px-3 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 flex items-center gap-2"><i class="ri-volume-mute-line"></i> Silenciar</button>`;
        }

        let readStatusHtml = isMe ? `<span class="read-check text-slate-300 ml-1 text-xs" title="Enviado"><i class="ri-check-line"></i></span>` : '';
        const blurClass = data.is_sensitive ? 'blur-content' : '';
        const overlay = data.is_sensitive ? `<div class="sensitive-overlay absolute inset-0 z-20 flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-2xl cursor-pointer border border-rose-100" onclick="this.parentElement.classList.add('sensitive-active')"><span class="text-xs font-bold text-rose-600 flex items-center gap-1.5 bg-rose-50 px-3 py-1.5 rounded-full"><i class="ri-eye-off-line"></i> Conteúdo Sensível</span></div>` : '';

        const reactionBtns = ['hug', 'candle', 'ear'].map(type => {
            const emoji = {'hug':'🫂', 'candle':'🕯️', 'ear':'👂'}[type];
            return `<button onclick="react(${data.id}, '${type}', this)" class="reaction-btn hover:bg-slate-50 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm"><span>${emoji}</span><span class="count hidden font-bold text-[10px]">0</span></button>`;
        }).join('');

        const escapedContent = escapeHtml(data.content).replace(/\n/g, '<br>');
        const displayName = data.is_anonymous ? 'Anónimo' : escapeHtml(data.user?.name || 'Alguém');

        // HTML final compatível com modo compacto (classes bubble-content e message-wrapper)
        div.innerHTML = `<div class="message-body max-w-[85%] md:max-w-[65%] flex flex-col ${isMe ? 'items-end' : 'items-start'}"><div class="relative group/bubble"><div class="bubble-content ${isMe ? 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-700 rounded-tl-none'} rounded-2xl shadow-sm px-4 py-3 text-[15px] md:text-base leading-relaxed ${blurClass}">${replyHtml}<span class="message-text">${escapedContent}</span></div><div class="absolute ${isMe ? '-left-8' : '-right-8'} top-2 opacity-0 group-hover/bubble:opacity-100 transition-opacity" x-data="{ open: false }"><button @click="open = !open" class="text-slate-300 hover:text-slate-500 p-1"><i class="ri-more-2-fill"></i></button><div x-show="open" @click.outside="open = false" style="display: none;" class="absolute ${isMe ? 'right-0' : 'left-0'} top-full mt-1 bg-white rounded-lg shadow-xl border border-slate-100 z-50 w-32 overflow-hidden py-1">${menuHtml}</div></div>${overlay}</div><div class="message-meta-container flex items-center gap-2 mt-1 px-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">${!isMe ? `<span class="text-[10px] font-bold text-slate-400">${displayName}</span>` : ''}<span class="text-[10px] text-slate-300 flex items-center message-meta">Agora ${readStatusHtml}</span><div class="flex items-center gap-1 ml-1 scale-90 md:scale-100 origin-${isMe ? 'right' : 'left'}">${reactionBtns}</div></div></div>`;
        document.getElementById('chat-messages').appendChild(div);
    }

    // --- Funções Auxiliares (Moderação e Utils) ---
    function updateMessageInDOM(message) {
        const msgEl = document.getElementById(`msg-${message.id}`);
        if(!msgEl) return;
        const textEl = msgEl.querySelector('.message-text');
        if(textEl) textEl.innerHTML = escapeHtml(message.content).replace(/\n/g, '<br>');
        const metaEl = msgEl.querySelector('.message-meta');
        if(metaEl && !metaEl.innerText.includes('(editado)')) metaEl.innerHTML = `(editado) ` + metaEl.innerHTML;
    }

    async function markMessagesAsRead() { if(document.visibilityState === 'visible') await axios.post(`/chat/${roomId}/read`); }
    function resizeTextarea(el) { el.style.height = 'auto'; el.style.height = (el.scrollHeight) + 'px'; if(el.value === '') el.style.height = '44px'; }
    
    // Funções mantidas do original (toggleSound, toggleDnd, etc.)
    function toggleSound(type) {
        const allAudios = document.querySelectorAll('audio'); const targetAudio = document.getElementById(`audio-${type}`); const allBtns = document.querySelectorAll('.sound-btn'); const nowPlayingText = document.getElementById('now-playing-text'); const nowPlayingTextMobile = document.getElementById('now-playing-text-mobile'); const nowPlayingHeader = document.getElementById('now-playing-header'); const soundNames = { 'rain': 'Chuva 🌧️', 'fire': 'Lareira 🔥', 'forest': 'Floresta 🌲' };
        if (!targetAudio.paused) { targetAudio.pause(); allBtns.forEach(btn => btn.classList.remove('active')); if(nowPlayingText) nowPlayingText.textContent = "Pausa para relaxar"; if(nowPlayingTextMobile) nowPlayingTextMobile.textContent = "Toque para ouvir"; if(nowPlayingHeader) nowPlayingHeader.classList.add('hidden'); }
        else { allAudios.forEach(a => { a.pause(); a.currentTime = 0; }); allBtns.forEach(btn => btn.classList.remove('active')); targetAudio.volume = 0.5; targetAudio.play(); const activeBtns = document.querySelectorAll(`#btn-${type}, #btn-${type}-mobile`); activeBtns.forEach(btn => btn.classList.add('active')); const text = `🎵 A tocar: ${soundNames[type]}`; if(nowPlayingText) nowPlayingText.textContent = text; if(nowPlayingTextMobile) nowPlayingTextMobile.textContent = text; if(nowPlayingHeader) { nowPlayingHeader.textContent = text; nowPlayingHeader.classList.remove('hidden'); } }
    }
    function toggleMobileMenu() { const d = document.getElementById('mobile-drawer'); const o = document.getElementById('mobile-overlay'); if (d.classList.contains('closed')) { d.classList.remove('closed'); d.classList.add('open'); o.classList.remove('hidden'); } else { d.classList.remove('open'); d.classList.add('closed'); o.classList.add('hidden'); } }
    function toggleDnd() { isDnd = !isDnd; const btn = document.getElementById('dnd-btn'); const txt = document.getElementById('dnd-text'); const c = document.getElementById('chat-container'); if(isDnd) { btn.classList.replace('bg-white', 'bg-indigo-100'); btn.classList.replace('text-slate-500', 'text-indigo-600'); btn.classList.replace('border-slate-200', 'border-indigo-200'); txt.textContent = "Em Pausa"; c.classList.add('pause-active'); } else { btn.classList.replace('bg-indigo-100', 'bg-white'); btn.classList.replace('text-indigo-600', 'text-slate-500'); btn.classList.replace('border-indigo-200', 'border-slate-200'); txt.textContent = "Pausa"; c.classList.remove('pause-active'); scrollToBottom(true); } }
    function updateCounters(val, inc = false) { const els = [document.getElementById('desktop-counter'), document.getElementById('mobile-counter'), document.getElementById('mobile-drawer-counter')]; let cur = parseInt(els[0]?.textContent || 0); let final = inc ? cur + val : val; final = Math.max(0, final); els.forEach(el => { if(el) el.textContent = final; }); }
    function addUserToSidebar(user) { const list = document.getElementById('online-users-list'); const listM = document.getElementById('users-list-mobile'); if(document.getElementById(`user-online-${user.id}`)) return; const isFoll = followingIds.includes(user.id); const bell = isFoll ? 'text-indigo-500 ri-notification-3-fill' : 'text-slate-300 ri-notification-3-line'; const btn = user.id !== currentUserId ? `<button onclick="toggleFollow(${user.id}, this)" class="ml-auto ${bell} hover:text-indigo-600 transition-colors"></button>` : ''; const html = `<div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold border border-indigo-200 shrink-0">${user.name.substring(0, 1)}</div><span class="text-sm font-medium text-slate-600 truncate flex-1">${user.name}</span>${btn}`; if(list) { const li = document.createElement('li'); li.id = `user-online-${user.id}`; li.className = 'flex items-center gap-2 animate-fade-in mb-2'; li.innerHTML = html; list.appendChild(li); } if(listM) { const liM = document.createElement('li'); liM.id = `user-mobile-${user.id}`; liM.className = 'flex items-center gap-2 animate-fade-in mb-2'; liM.innerHTML = html; listM.appendChild(liM); } }
    function removeUserFromSidebar(user) { const el = document.getElementById(`user-online-${user.id}`); if (el) el.remove(); const elM = document.getElementById(`user-mobile-${user.id}`); if (elM) elM.remove(); }
    window.toggleFollow = async function(id, btn) { try { if(btn.classList.contains('ri-notification-3-line')) { btn.classList.replace('ri-notification-3-line', 'ri-notification-3-fill'); btn.classList.replace('text-slate-300', 'text-indigo-500'); if(!followingIds.includes(id)) followingIds.push(id); } else { btn.classList.replace('ri-notification-3-fill', 'ri-notification-3-line'); btn.classList.replace('text-indigo-500', 'text-slate-300'); const idx = followingIds.indexOf(id); if(idx > -1) followingIds.splice(idx, 1); } await axios.post(`/chat/${roomId}/follow/${id}`); } catch(e) { alert("Erro."); } };
    window.reportMessage = async function(id) { const r = prompt("Motivo?"); if(r) { try { await axios.post(`/chat/messages/${id}/report`, { reason: r }); alert("Obrigado."); } catch(e) { alert("Erro."); } } };
    window.muteUser = async function(id) { if(confirm('Silenciar 10m?')) try { await axios.post(`/chat/${roomId}/mute/${id}`); alert("Silenciado."); } catch(e) { alert("Erro."); } };
    window.deleteMessage = async function(e, id) { e.preventDefault(); if(confirm('Apagar?')) try { await axios.delete(`/chat/messages/${id}`); } catch(err) { alert("Erro."); } };
    window.react = async function(mid, type, btn) { const span = btn.querySelector('.count'); let c = parseInt(span.textContent) || 0; span.textContent = c + 1; span.classList.remove('hidden'); btn.classList.add('scale-125', 'bg-indigo-50'); setTimeout(() => btn.classList.remove('scale-125', 'bg-indigo-50'), 200); try { await axios.post(`/chat/${roomId}/message/${mid}/react`, { type }); } catch (e) { span.textContent = c; if(c===0) span.classList.add('hidden'); } };
    function updateReactionUI(mid, type, count) { const el = document.getElementById(`msg-${mid}`); if(!el) return; const btn = el.querySelector(`button[onclick*="'${type}'"]`); if(btn) { const s = btn.querySelector('.count'); s.textContent = count; count > 0 ? s.classList.remove('hidden') : s.classList.add('hidden'); } }
    function showTypingIndicator(name) { const i = document.getElementById('typing-indicator'); if(!i) return; i.innerText = `${name} está a escrever...`; i.classList.remove('opacity-0'); clearTimeout(typingTimer); typingTimer = setTimeout(() => i.classList.add('opacity-0'), 3000); }
    function triggerSupportEffect(type) { if (type === 'hug') { document.body.classList.add('feel-hug-effect'); setTimeout(() => document.body.classList.remove('feel-hug-effect'), 2000); for(let i=0; i<5; i++) setTimeout(() => createFloatingParticle('❤️'), i * 200); showToast("Recebeste um abraço virtual."); } else if (type === 'candle') { for(let i=0; i<5; i++) setTimeout(() => createFloatingParticle('✨'), i * 300); showToast("Alguém acendeu uma luz por ti."); } else if (type === 'ear') { showToast("Alguém está a ouvir-te."); } }
    function createFloatingParticle(emoji) { const l = document.getElementById('visual-effects-layer'); if(!l) return; const el = document.createElement('div'); el.classList.add('floating-heart'); el.innerText = emoji; el.style.left = (Math.floor(Math.random() * 80) + 10) + '%'; l.appendChild(el); setTimeout(() => el.remove(), 3000); }
    function showToast(txt, alert = false) { const t = document.getElementById('support-toast'); if(!t) return; const c = t.querySelector('#toast-content'); if(alert) c.innerHTML = `<p class="text-sm font-bold text-indigo-800">Alerta</p><p class="text-xs text-indigo-600">${txt}</p>`; else c.innerHTML = `<p class="text-sm font-bold text-slate-800">Sentiste isso?</p><p class="text-xs text-slate-500">${txt}</p>`; t.classList.add('active'); setTimeout(() => t.classList.remove('active'), 4000); }
    function updateCrisisUI(active) { isCrisisMode = active; const btn = document.getElementById('crisis-btn'); const btnM = document.getElementById('crisis-btn-mobile'); let b = document.getElementById('crisis-mode-banner'); const u = (x) => { if(x) { if(active){ x.classList.remove('bg-slate-800'); x.classList.add('bg-rose-600','animate-pulse'); x.querySelector('span').innerText = "DESATIVAR CRISE"; } else { x.classList.add('bg-slate-800'); x.classList.remove('bg-rose-600','animate-pulse'); x.querySelector('span').innerText = "MODO CRISE"; } } }; u(btn); u(btnM); if(active) { if(!b) { const d=document.createElement('div'); d.id='crisis-mode-banner'; d.className='fixed top-20 left-1/2 transform -translate-x-1/2 bg-rose-600 text-white px-6 py-2 rounded-full shadow-xl z-50 flex items-center gap-3 animate-bounce-in font-bold text-sm'; d.innerHTML=`<i class="ri-alarm-warning-fill"></i> <span>MODO CRISE ATIVO: Chat lento.</span>`; document.body.appendChild(d); } } else if(b) b.remove(); }
</script>