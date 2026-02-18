<script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // ==========================================
        // 1. DARK MODE TOGGLE
        // ==========================================
        const themeToggleBtn = document.getElementById('theme-toggle');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');

        // Verificar preferência inicial (localStorage ou Sistema)
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            lightIcon.classList.remove('hidden');
        } else {
            document.documentElement.classList.remove('dark');
            darkIcon.classList.remove('hidden');
        }

        if(themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                // Alternar ícones
                darkIcon.classList.toggle('hidden');
                lightIcon.classList.toggle('hidden');

                // Alternar classe e guardar preferência
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            });
        }

        // ==========================================
        // 2. SCROLL REVEAL ANIMATION
        // ==========================================
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1 // Dispara quando 10% do elemento está visível
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-up');
                    entry.target.classList.remove('opacity-0', 'translate-y-8'); 
                    observer.unobserve(entry.target); // Só anima uma vez
                }
            });
        }, observerOptions);

        // Aplica a todos os elementos com a classe .scroll-reveal
        document.querySelectorAll('.scroll-reveal').forEach((el) => {
            el.classList.add('opacity-0', 'translate-y-8', 'transition-all', 'duration-700'); // Estado inicial
            observer.observe(el);
        });

        // ==========================================
        // 3. MODAL SOS
        // ==========================================
        const allButtons = document.querySelectorAll('button');
        const sosBtn = Array.from(allButtons).find(btn => btn.textContent.includes('SOS'));
        const modal = document.getElementById('sosModal');
        const overlay = document.getElementById('modalOverlay');
        const closeBtn = document.getElementById('modalClose');

        function toggleModal() { 
            if(modal) modal.classList.toggle('hidden'); 
        }
        
        if(sosBtn) sosBtn.addEventListener('click', toggleModal);
        if(overlay) overlay.addEventListener('click', toggleModal);
        if(closeBtn) closeBtn.addEventListener('click', toggleModal);

        // ==========================================
        // 4. MENU MOBILE
        // ==========================================
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        if(mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', () => { 
                mobileMenu.classList.toggle('hidden'); 
            });
            
            mobileLinks.forEach(link => { 
                link.addEventListener('click', () => { 
                    mobileMenu.classList.add('hidden'); 
                }); 
            });
        }

        // ==========================================
        // 5. PLAYER DE ÁUDIO
        // ==========================================
        let currentAudio = null; 
        let currentBtn = null;
        
        const soundButtons = document.querySelectorAll('.sound-btn');
        const floatingPlayer = document.getElementById('floatingPlayer');
        const playerTitle = document.getElementById('playerTitle');
        const playerControlBtn = document.getElementById('playerControlBtn');
        const playerCloseBtn = document.getElementById('playerCloseBtn');

        function updateIcons(state) {
            if (!currentBtn) return;
            
            const cardIcon = currentBtn.querySelector('.play-icon');
            const floatIcon = playerControlBtn ? playerControlBtn.querySelector('i') : null;

            if (state === 'play') {
                if(cardIcon) { 
                    cardIcon.classList.remove('ri-play-circle-fill'); 
                    cardIcon.classList.add('ri-pause-circle-fill'); 
                }
                if(floatIcon) floatIcon.className = 'ri-pause-fill text-lg';
            } else if (state === 'pause' || state === 'reset') {
                if(cardIcon) { 
                    cardIcon.classList.remove('ri-pause-circle-fill'); 
                    cardIcon.classList.add('ri-play-circle-fill'); 
                }
                if(state === 'pause' && floatIcon) floatIcon.className = 'ri-play-fill text-lg';
            }
        }

        soundButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const soundPath = btn.getAttribute('data-sound');
                const soundName = btn.getAttribute('data-sound-name');
                
                // Se clicarmos no mesmo botão
                if (currentAudio && currentBtn === btn) {
                    if (currentAudio.paused) { 
                        currentAudio.play(); 
                        updateIcons('play'); 
                    } else { 
                        currentAudio.pause(); 
                        updateIcons('pause'); 
                    }
                    return;
                }

                // Se houver outro a tocar, parar
                if (currentAudio) { 
                    currentAudio.pause(); 
                    updateIcons('reset'); 
                }

                // Novo som
                currentAudio = new Audio(soundPath);
                currentBtn = btn;
                
                if(playerTitle) playerTitle.textContent = soundName;
                if(floatingPlayer) floatingPlayer.classList.remove('hidden');

                currentAudio.play().then(() => {
                    updateIcons('play');
                }).catch(error => {
                    console.log("Erro ao tocar:", error);
                });

                currentAudio.onended = () => {
                    updateIcons('reset');
                    if(floatingPlayer) floatingPlayer.classList.add('hidden');
                    currentAudio = null;
                    currentBtn = null;
                };
            });
        });

        if(playerControlBtn) {
            playerControlBtn.addEventListener('click', () => {
                if (currentAudio) {
                    if (currentAudio.paused) { 
                        currentAudio.play(); 
                        updateIcons('play'); 
                    } else { 
                        currentAudio.pause(); 
                        updateIcons('pause'); 
                    }
                }
            });
        }

        if(playerCloseBtn) {
            playerCloseBtn.addEventListener('click', () => {
                if (currentAudio) {
                    currentAudio.pause();
                    currentAudio.currentTime = 0;
                    updateIcons('reset');
                    if(floatingPlayer) floatingPlayer.classList.add('hidden');
                    currentAudio = null;
                    currentBtn = null;
                }
            });
        }
    });

    // ==========================================
    // 6. BREATHE WIDGET (Global Scope)
    // ==========================================
    let breatheInterval; 
    let isBreathing = false;
    
    function toggleBreathing() {
        const circle = document.getElementById('breathe-circle');
        const ring1 = document.getElementById('breathe-ring-1');
        const ring2 = document.getElementById('breathe-ring-2'); // Se existir no HTML
        const icon = document.getElementById('breathe-icon');
        const textSpan = document.getElementById('breathe-text');
        const title = document.getElementById('breathe-instruction');
        const sub = document.getElementById('breathe-sub');

        if (isBreathing) {
            // STOP
            clearInterval(breatheInterval); 
            isBreathing = false;
            
            icon.classList.remove('hidden'); 
            textSpan.classList.add('hidden');
            
            circle.style.transform = 'scale(1)'; 
            ring1.style.transform = 'scale(1)';
            
            title.innerText = "Pausa terminada"; 
            sub.innerText = "Clica para recomeçar";
            
            setTimeout(() => { 
                if(title) title.innerText = "Precisas de uma pausa?"; 
            }, 2000);
            
        } else {
            // START
            isBreathing = true; 
            icon.classList.add('hidden'); 
            textSpan.classList.remove('hidden');
            
            let phase = 0; // 0: Inhale, 1: Hold, 2: Exhale
            
            function runPhase() {
                if(!isBreathing) return;

                if (phase === 0) {
                    title.innerText = "Inspira..."; 
                    sub.innerText = "Enche os pulmões"; 
                    textSpan.innerText = "Inspira";
                    
                    circle.style.transform = 'scale(1.5)'; 
                    ring1.style.transform = 'scale(1.8)'; 
                    ring1.style.borderColor = 'rgba(255,255,255,0.5)';
                    
                    phase = 1;
                } else if (phase === 1) {
                    title.innerText = "Segura..."; 
                    sub.innerText = "Mantém o ar"; 
                    textSpan.innerText = "Segura";
                    // Mantém tamanho
                    phase = 2;
                } else {
                    title.innerText = "Expira..."; 
                    sub.innerText = "Deita tudo cá para fora"; 
                    textSpan.innerText = "Expira";
                    
                    circle.style.transform = 'scale(1)'; 
                    ring1.style.transform = 'scale(1)'; 
                    ring1.style.borderColor = 'rgba(255,255,255,0.1)';
                    
                    phase = 0;
                }
            }

            runPhase(); 
            breatheInterval = setInterval(runPhase, 4000);
        }
    }
</script>