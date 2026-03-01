{{--
    Componente de card com suporte a gestos mobile.

    Swipe esquerda: Guardar/Bookmark
    Swipe direita: Reagir (abraço)

    Props:
    - $postId: ID do post para as ações
    - $slot: Conteúdo do card
--}}
@props(['postId'])

<div x-data="swipeableCard({{ $postId }})"
     @touchstart.passive="onTouchStart($event)"
     @touchmove.passive="onTouchMove($event)"
     @touchend="onTouchEnd($event)"
     class="relative overflow-hidden"
     :style="{ transform: `translateX(${offsetX}px)`, transition: isDragging ? 'none' : 'transform 0.3s ease' }">

    {{-- Indicadores de swipe (fundo) --}}
    <div class="absolute inset-y-0 left-0 w-20 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/30 rounded-l-2xl transition-opacity"
         :class="offsetX > 30 ? 'opacity-100' : 'opacity-0'">
        <i class="ri-heart-line text-emerald-500 text-xl"></i>
    </div>
    <div class="absolute inset-y-0 right-0 w-20 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 rounded-r-2xl transition-opacity"
         :class="offsetX < -30 ? 'opacity-100' : 'opacity-0'">
        <i class="ri-bookmark-line text-indigo-500 text-xl"></i>
    </div>

    {{-- Conteúdo --}}
    <div class="relative z-10 bg-white dark:bg-slate-800">
        {{ $slot }}
    </div>
</div>

@once
<script>
    function swipeableCard(postId) {
        return {
            startX: 0,
            offsetX: 0,
            isDragging: false,
            threshold: 80,

            onTouchStart(e) {
                this.startX = e.touches[0].clientX;
                this.isDragging = true;
            },

            onTouchMove(e) {
                if (!this.isDragging) return;
                const diff = e.touches[0].clientX - this.startX;
                this.offsetX = Math.max(-120, Math.min(120, diff));
            },

            onTouchEnd() {
                this.isDragging = false;

                if (this.offsetX > this.threshold) {
                    // Swipe direita = Reagir
                    if (typeof window.react === 'function') {
                        const btn = document.querySelector(`#post-card-${postId} .react-hug`);
                        if (btn) window.react(postId, 'hug', btn);
                    }
                } else if (this.offsetX < -this.threshold) {
                    // Swipe esquerda = Guardar
                    if (typeof window.toggleSave === 'function') {
                        const btn = document.querySelector(`#post-card-${postId} .save-btn`);
                        if (btn) window.toggleSave(postId, btn);
                    }
                }

                this.offsetX = 0;
            }
        }
    }
</script>
@endonce
