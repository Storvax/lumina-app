<main id="chat-container" class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 scroll-smooth pb-20 md:pb-8 transition-all duration-500">
    @if($messages->isEmpty())
    <div id="empty-state" class="flex flex-col items-center justify-center h-full opacity-60 mt-4">
        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300"><i class="ri-cup-line text-4xl"></i></div>
        <p class="text-slate-500 font-medium">A fogueira está calma.</p>
    </div>
    @endif

    <div id="chat-messages">
        @foreach($messages as $message)
            @php 
                $isMe = $message->user_id === Auth::id();
                $hugs = $message->reactions->where('type', 'hug')->count();
                $candles = $message->reactions->where('type', 'candle')->count();
                $ears = $message->reactions->where('type', 'ear')->count();
                $isRead = $message->reads->where('user_id', '!=', Auth::id())->isNotEmpty();
            @endphp
            <div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} animate-fade-up group relative" id="msg-{{ $message->id }}">
                <div class="max-w-[85%] md:max-w-[65%] flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                    <div class="relative group/bubble">
                        @if($message->is_sensitive)
                            <div class="sensitive-overlay absolute inset-0 z-20 flex items-center justify-center bg-white/90 backdrop-blur-sm rounded-2xl cursor-pointer border border-rose-100" onclick="this.parentElement.classList.add('sensitive-active')">
                                <span class="text-xs font-bold text-slate-800 bg-white/95 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1 border border-slate-100"><i class="ri-eye-off-line text-rose-500"></i> Sensível</span>
                            </div>
                        @endif
                        
                        <div class="{{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-700 rounded-tl-none' }} rounded-2xl shadow-sm px-4 py-3 text-[15px] md:text-base leading-relaxed {{ $message->is_sensitive ? 'blur-content' : '' }}">
                            @if($message->replyTo)
                                <div class="mb-2 text-xs border-l-2 {{ $isMe ? 'border-indigo-300 bg-indigo-700/30 text-indigo-100' : 'border-indigo-500 bg-slate-100 text-slate-500' }} pl-2 py-1 rounded-r opacity-90 cursor-pointer hover:opacity-100 transition-opacity" onclick="document.getElementById('msg-{{ $message->reply_to_id }}').scrollIntoView({behavior: 'smooth', block: 'center'})">
                                    <span class="font-bold block text-[10px] uppercase tracking-wide opacity-80">
                                        {{ $message->replyTo->user_id == Auth::id() ? 'Ti' : ($message->replyTo->is_anonymous ? 'Anónimo' : $message->replyTo->user->name) }}
                                    </span>
                                    <span class="truncate block max-w-[200px] italic">
                                        {{ Str::limit($message->replyTo->content, 40) }}
                                    </span>
                                </div>
                            @endif
                            <span class="message-text">{!! nl2br(e($message->content)) !!}</span>
                        </div>

                        <div class="absolute {{ $isMe ? '-left-8' : '-right-8' }} top-2 opacity-0 group-hover/bubble:opacity-100 transition-opacity" x-data="{ open: false }">
                            <button @click="open = !open" class="text-slate-300 hover:text-slate-500 p-1"><i class="ri-more-2-fill"></i></button>
                            <div x-show="open" @click.outside="open = false" style="display: none;" class="absolute {{ $isMe ? 'right-0' : 'left-0' }} top-full mt-1 bg-white rounded-lg shadow-xl border border-slate-100 z-50 w-36 overflow-hidden py-1">
                                <button onclick="startReply({{ $message->id }}, '{{ $isMe ? 'ti mesmo' : ($message->is_anonymous ? 'Anónimo' : e($message->user->name)) }}', '{{ Str::limit(e($message->content), 30) }}...')" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2">
                                    <i class="ri-reply-line"></i> Responder
                                </button>
                                @if(auth()->id() === $message->user_id || auth()->user()->isModerator())
                                    @if(auth()->id() === $message->user_id && $message->created_at->diffInMinutes(now()) <= 5)
                                        <button onclick="startEdit({{ $message->id }}, '{{ e($message->content) }}')" class="w-full text-left px-3 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50 flex items-center gap-2"><i class="ri-pencil-line"></i> Editar</button>
                                    @endif
                                    <form onsubmit="deleteMessage(event, {{ $message->id }})" class="block"><button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 flex items-center gap-2"><i class="ri-delete-bin-line"></i> Apagar</button></form>
                                @endif
                                @if(auth()->id() !== $message->user_id)
                                    <button onclick="reportMessage({{ $message->id }})" class="w-full text-left px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 flex items-center gap-2"><i class="ri-flag-line"></i> Denunciar</button>
                                    @if(auth()->user()->isModerator())
                                        <div class="h-px bg-slate-100 my-1"></div><button onclick="muteUser({{ $message->user_id }})" class="w-full text-left px-3 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 flex items-center gap-2"><i class="ri-volume-mute-line"></i> Silenciar</button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-1 px-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                        @if(!$isMe)<span class="text-[10px] font-bold text-slate-400">{{ $message->is_anonymous ? 'Anónimo' : $message->user->name }}</span>@endif
                        <span class="text-[10px] text-slate-300 flex items-center gap-1 message-meta">
                            @if($message->edited_at) <span class="italic text-[9px]">(editado)</span> @endif
                            {{ $message->created_at->format('H:i') }}
                            @if($isMe)
                                <span class="read-check ml-0.5 text-xs {{ $isRead ? 'text-blue-500' : 'text-slate-300' }}" title="{{ $isRead ? 'Lido' : 'Enviado' }}">
                                    <i class="{{ $isRead ? 'ri-check-double-line' : 'ri-check-line' }}"></i>
                                </span>
                            @endif
                        </span>
                        <div class="flex items-center gap-1 ml-1 scale-90 md:scale-100 origin-{{ $isMe ? 'right' : 'left' }}">
                            <button onclick="react({{ $message->id }}, 'hug', this)" class="reaction-btn hover:bg-rose-50 hover:text-rose-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm"><span>🫂</span><span class="count {{ $hugs > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $hugs }}</span></button>
                            <button onclick="react({{ $message->id }}, 'candle', this)" class="reaction-btn hover:bg-amber-50 hover:text-amber-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm"><span>🕯️</span><span class="count {{ $candles > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $candles }}</span></button>
                            <button onclick="react({{ $message->id }}, 'ear', this)" class="reaction-btn hover:bg-blue-50 hover:text-blue-600 rounded-full px-1.5 py-0.5 text-xs transition-all flex items-center gap-1 bg-white border border-slate-100 text-slate-400 shadow-sm"><span>👂</span><span class="count {{ $ears > 0 ? '' : 'hidden' }} font-bold text-[10px]">{{ $ears }}</span></button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div id="scroll-anchor"></div>
</main>

<div class="p-4 bg-white border-t border-slate-100">
    <div id="typing-indicator" class="text-xs text-slate-400 italic h-4 mb-2 transition-opacity opacity-0 pl-4"></div>
    <form id="chat-form" class="relative flex items-end gap-2">
        <div class="flex items-center gap-1 mb-2">
            <button type="button" id="cw-btn" class="text-slate-400 hover:text-rose-500 p-2 rounded-full hover:bg-slate-50 transition-colors" title="Conteúdo Sensível"><i class="ri-eye-off-line text-lg"></i></button>
            <div class="relative group" title="Modo Anónimo">
                <input type="checkbox" id="anonymous-toggle" class="peer sr-only">
                <label for="anonymous-toggle" class="cursor-pointer text-slate-400 peer-checked:text-indigo-600 p-2 block hover:bg-slate-50 rounded-full"><i class="ri-spy-line text-lg"></i></label>
            </div>
        </div>
        <textarea id="messageInput" rows="1" class="w-full bg-slate-50 border-0 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all resize-none max-h-32" placeholder="Escreve a tua mensagem..."></textarea>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-xl transition-all shadow-lg shadow-indigo-200 hover:scale-105 active:scale-95 flex-shrink-0"><i class="ri-send-plane-fill text-xl"></i></button>
    </form>
</div>