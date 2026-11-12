<footer class="modern-footer">
    <div class="container py-5">
        <div class="row g-4">
            <!-- Logo et description -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-3">
                    <i class="bi bi-check2-square me-2"></i>
                    <span class="fw-bold">Gestion des Tâches</span>
                </div>
                <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.6;">
                    Plateforme professionnelle pour organiser vos projets, 
                    collaborer efficacement et booster votre productivité au quotidien.
                </p>
                <div class="social-links">
                    <a href="https://wa.me/+22897608323" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                  
                    <a href="https://github.com/josquemtech" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                        <i class="bi bi-github"></i>
                    </a>
                    <a href="mailto:josquem.k@gmail.com" class="social-link" aria-label="Email">
                        <i class="bi bi-envelope-fill"></i>
                    </a>
                </div>
            </div>

            <!-- Liens rapides -->
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Navigation</h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="/"><i class="bi bi-house me-2"></i>Accueil</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="/my_task.php"><i class="bi bi-list-task me-2"></i>Mes tâches</a></li>
                    <li><a href="/profile.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><a href="/notifications.php"><i class="bi bi-bell me-2"></i>Notifications</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Support</h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="/help.php"><i class="bi bi-question-circle me-2"></i>Aide</a></li>
                    <li><a href="contact.php"><i class="bi bi-envelope me-2"></i>Contact</a></li>
                    <li><a href="/faq.php"><i class="bi bi-chat-dots me-2"></i>FAQ</a></li>
                </ul>
            </div>

            <!-- Légal -->
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Légal</h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="/about.php"><i class="bi bi-info-circle me-2"></i>À propos</a></li>
                    <li><a href="/terms.php"><i class="bi bi-file-text me-2"></i>CGU</a></li>
                    <li><a href="/privacy.php"><i class="bi bi-shield-check me-2"></i>Confidentialité</a></li>
                </ul>
            </div>

            <!-- Contact direct -->
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Contact</h6>
                <ul class="footer-links list-unstyled">
                    <li>
                        <a href="mailto:josquem.k@gmail.com">
                            <i class="bi bi-envelope me-2"></i>josquem.k@gmail.com
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/josquemtech" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-github me-2"></i>@josquemtech
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Divider -->
        <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

        <!-- Copyright -->
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-muted" style="font-size: 0.85rem;">
                    &copy; <?= date('Y') ?> <strong>@JOSQUEM</strong> - Gestion des Tâches. Tous droits réservés.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <span class="badge bg-light text-dark px-3 py-2" style="font-size: 0.75rem; font-weight: 500;">
                    <i class="bi bi-code-slash me-1"></i>Version 1.0.0
                </span>
            </div>
        </div>
    </div>
</footer>

<style>
.modern-footer {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: #cbd5e1;
    margin-top: auto;
}

.footer-brand {
    font-size: 1.25rem;
    color: white;
    display: flex;
    align-items: center;
}

.footer-brand i {
    font-size: 1.5rem;
    color: #667eea;
}

.footer-title {
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 1.25rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.footer-links li {
    margin-bottom: 0.75rem;
}

.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}

.footer-links a:hover {
    color: #667eea;
    transform: translateX(4px);
}

.footer-links a i {
    font-size: 0.85rem;
}

.social-links {
    display: flex;
    gap: 0.75rem;
}

.social-link {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 1.2rem;
}

.social-link:hover {
    background: #667eea;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    border-color: #667eea;
}

/* Couleurs spécifiques par réseau au survol */
.social-link[aria-label="WhatsApp"]:hover {
    background: #25D366;
    border-color: #25D366;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
}

.social-link[aria-label="LinkedIn"]:hover {
    background: #0A66C2;
    border-color: #0A66C2;
    box-shadow: 0 4px 12px rgba(10, 102, 194, 0.4);
}

.social-link[aria-label="GitHub"]:hover {
    background: #333;
    border-color: #333;
    box-shadow: 0 4px 12px rgba(51, 51, 51, 0.4);
}

.social-link[aria-label="Email"]:hover {
    background: #EA4335;
    border-color: #EA4335;
    box-shadow: 0 4px 12px rgba(234, 67, 53, 0.4);
}

@media (max-width: 768px) {
    .modern-footer {
        text-align: center;
    }
    
    .footer-brand {
        justify-content: center;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .footer-links a:hover {
        transform: none;
    }
}
</style>

<!-- Toast Notifications -->
<?php if(isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    <?php if(isset($_SESSION['success'])): ?>
    <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php if (!empty($GLOBALS['headerRendered'])): ?>
</main>
<?php endif; ?>

<?php if(isset($_SESSION['user_id'])): ?>
<button id="josIaToggle" type="button" class="jos-ia-toggle" aria-label="Ouvrir JOS IA" title="JOS IA">
    <i class="bi bi-robot"></i>
</button>
<aside id="josIaPanel" class="jos-ia-panel" aria-label="Assistant JOS IA" hidden>
    <div class="jos-ia-header">
        <div><i class="bi bi-stars me-2"></i><strong>JOS IA</strong></div>
        <button id="josIaClose" type="button" class="btn-close btn-close-white" aria-label="Fermer"></button>
    </div>
    <div id="josIaMessages" class="jos-ia-messages" aria-live="polite">
        <div class="jos-ia-message assistant">Bonjour, je suis JOS IA. Comment puis-je vous guider ?</div>
    </div>
    <form id="josIaForm" class="jos-ia-form">
        <input id="josIaInput" type="text" class="form-control" placeholder="Posez votre question..." autocomplete="off" maxlength="1000" required>
        <button id="josIaSend" class="btn btn-primary" type="submit" aria-label="Envoyer"><i class="bi bi-send"></i></button>
    </form>
</aside>
<style>
.jos-ia-toggle{position:fixed;right:24px;bottom:24px;width:58px;height:58px;border:0;border-radius:50%;background:#4f46e5;color:#fff;z-index:1060;box-shadow:0 8px 24px rgba(79,70,229,.35);font-size:1.45rem}
.jos-ia-panel{position:fixed;right:24px;bottom:92px;width:min(360px,calc(100vw - 32px));height:480px;max-height:calc(100vh - 120px);background:var(--app-surface,#fff);color:var(--app-text,#172033);border:1px solid rgba(148,163,184,.3);border-radius:18px;z-index:1060;box-shadow:0 16px 40px rgba(15,23,42,.25);overflow:hidden}
.jos-ia-header{height:58px;padding:0 16px;display:flex;align-items:center;justify-content:space-between;background:#4f46e5;color:#fff}
.jos-ia-messages{height:362px;padding:14px;overflow-y:auto}
.jos-ia-message{max-width:85%;padding:9px 12px;margin-bottom:10px;border-radius:12px;font-size:.88rem}
.jos-ia-message.assistant{background:#eef2ff;color:#312e81;border-bottom-left-radius:4px}
.jos-ia-message.user{margin-left:auto;background:#4f46e5;color:#fff;border-bottom-right-radius:4px}
.jos-ia-form{height:60px;display:flex;gap:8px;padding:8px;border-top:1px solid rgba(148,163,184,.25)}
.jos-ia-form .form-control{border-radius:999px}
.jos-ia-form .btn{width:44px;border-radius:50%}
.jos-ia-message.typing{display:inline-flex;align-items:center;gap:4px;color:#64748b}
.jos-ia-message.typing span{width:6px;height:6px;border-radius:50%;background:currentColor;animation:josTyping 1s infinite ease-in-out}
.jos-ia-message.typing span:nth-child(2){animation-delay:.15s}
.jos-ia-message.typing span:nth-child(3){animation-delay:.3s}
@keyframes josTyping{0%,80%,100%{opacity:.25;transform:translateY(0)}40%{opacity:1;transform:translateY(-2px)}}
.jos-ia-form .form-control:disabled{opacity:.6}
html[data-app-theme="dark"] .jos-ia-panel{background:#0f172a;color:#e2e8f0;border-color:#334155}
html[data-app-theme="dark"] .jos-ia-message.assistant{background:#1e293b;color:#e2e8f0}
html[data-app-theme="dark"] .jos-ia-form{border-top-color:#334155}
</style>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Initialiser les toasts Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    const toastElList = document.querySelectorAll('.toast');
    toastElList.forEach(function(toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    });
});

let notificationPollTimer = null;
const checkNewNotifications = async function () {
    const bellButton = document.querySelector('.header-icon-btn');
    // Pas de cloche (pages publiques) : inutile d'interroger l'API.
    if (!bellButton) return;
    try {
        const response = await fetch('/app/Model/notifications.php?json=1', {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });
        if (!response.ok) return;
        const data = await response.json();
        const notifications = data.notifications || [];

        // Met à jour le badge de la cloche, même sans notification navigateur.
        let badge = bellButton.querySelector('.notification-badge');
        if (notifications.length > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                bellButton.appendChild(badge);
            }
            badge.textContent = notifications.length;
        } else if (badge) {
            badge.remove();
        }

        if (!('Notification' in window) || Notification.permission !== 'granted') return;

        const seen = JSON.parse(localStorage.getItem('seenTaskNotifications') || '[]');
        for (const notification of notifications) {
            if (seen.includes(String(notification.id))) continue;
            new Notification('Nouvelle notification', {
                body: notification.message,
                tag: 'notification-' + notification.id
            });
            seen.push(String(notification.id));
        }
        localStorage.setItem('seenTaskNotifications', JSON.stringify(seen.slice(-100)));
    } catch (_) {}
};
checkNewNotifications();
notificationPollTimer = window.setInterval(checkNewNotifications, 30000);

const josToggle = document.getElementById('josIaToggle');
const josPanel = document.getElementById('josIaPanel');
if (josToggle && josPanel) {
    const josMessages = document.getElementById('josIaMessages');
    const josInput = document.getElementById('josIaInput');
    const josSend = document.getElementById('josIaSend');
    const josForm = document.getElementById('josIaForm');
    let josHistoryLoaded = false;
    let josBusy = false;

    const josAppend = (sender, text) => {
        const bubble = document.createElement('div');
        bubble.className = 'jos-ia-message ' + sender;
        bubble.textContent = text;
        josMessages.appendChild(bubble);
        josMessages.scrollTop = josMessages.scrollHeight;
        return bubble;
    };

    const josTyping = () => {
        const bubble = document.createElement('div');
        bubble.className = 'jos-ia-message assistant typing';
        bubble.innerHTML = '<span></span><span></span><span></span>';
        josMessages.appendChild(bubble);
        josMessages.scrollTop = josMessages.scrollHeight;
        return bubble;
    };

    const josLoadHistory = async () => {
        josHistoryLoaded = true;
        try {
            const response = await fetch('/app/Model/jos-chat.php', {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) return;
            const data = await response.json();
            const history = Array.isArray(data.messages) ? data.messages : [];
            if (!history.length) return;
            josMessages.innerHTML = '';
            history.forEach((item) => {
                josAppend(item.sender === 'user' ? 'user' : 'assistant', String(item.message || ''));
            });
        } catch (_) {}
    };

    josToggle.addEventListener('click', () => {
        josPanel.hidden = !josPanel.hidden;
        if (!josPanel.hidden) {
            if (!josHistoryLoaded) josLoadHistory();
            josInput?.focus();
        }
    });

    document.getElementById('josIaClose')?.addEventListener('click', () => {
        josPanel.hidden = true;
    });

    josForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (josBusy) return;
        const text = josInput.value.trim();
        if (!text) return;

        josBusy = true;
        josInput.value = '';
        josInput.disabled = true;
        if (josSend) josSend.disabled = true;
        josAppend('user', text);
        const typingBubble = josTyping();

        try {
            const response = await fetch('/app/Model/jos-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const raw = await response.text();
            let data = {};
            try { data = JSON.parse(raw); } catch (_) {}
            typingBubble.remove();
            if (!response.ok) {
                josAppend('assistant', data.message || 'Votre session a peut-être expiré. Reconnectez-vous puis réessayez.');
            } else {
                josAppend('assistant', data.message || 'Je n’ai pas pu répondre.');
            }
        } catch (_) {
            typingBubble.remove();
            josAppend('assistant', 'Impossible de joindre JOS IA pour le moment. Vérifiez votre connexion puis réessayez.');
        } finally {
            josBusy = false;
            josInput.disabled = false;
            if (josSend) josSend.disabled = false;
            josInput.focus();
        }
    });
}
</script>

</body>
</html>