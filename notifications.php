<?php
require_once __DIR__ . '/app/Model/notifications.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$unreadCount = 0;
foreach ($notifications as $notification) {
    if (empty($notification['is_read'])) {
        $unreadCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Gestion des Tâches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .notification-item { transition: background-color .2s ease; }
        .notification-item.unread { background-color: #f8faff; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/app/inc/nav.php'; ?>

    <main class="main-content py-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0">Notifications</h1>
                <button id="mark-all-read" class="btn btn-outline-secondary" type="button" <?= $unreadCount === 0 ? 'disabled' : '' ?>>
                    <i class="bi bi-check2-all me-2"></i>Tout marquer comme lu
                </button>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom fw-bold">Vos notifications</div>
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Aucune notification</h4>
                            <p class="text-muted mb-0">Vous n'avez aucune notification pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($notifications as $notification): ?>
                                <?php
                                $highlight = (string)($_GET['notification_id'] ?? '') === (string)$notification['id'];
                                $notificationType = $notification['notification_type'] ?? 'general';
                                $typeLabels = [
                                    'task_created' => ['Nouvelle tâche', 'bi-plus-circle'],
                                    'task_reminder' => ['Rappel de tâche', 'bi-clock-history'],
                                    'task_change_request' => ['Demande de modification', 'bi-pencil-square'],
                                    'completion_proof' => ['Preuve de fin', 'bi-file-earmark-check'],
                                    'completion_reviewed' => ['Résultat de validation', 'bi-check2-circle'],
                                    'general' => ['Système', 'bi-info-circle'],
                                ];
                                [$typeLabel, $typeIcon] = $typeLabels[$notificationType] ?? $typeLabels['general'];
                                ?>
                                <button type="button"
                                        class="list-group-item list-group-item-action notification-item notification-detail-button text-start <?= empty($notification['is_read']) ? 'unread border-start border-3 border-primary' : 'border-start border-3 border-light-subtle' ?> <?= $highlight ? 'border-primary shadow-sm' : '' ?>"
                                        data-notification-id="<?= (int)$notification['id'] ?>"
                                        data-message="<?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-date="<?= htmlspecialchars(!empty($notification['created_at']) ? date('d/m/Y à H:i', strtotime($notification['created_at'])) : '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-link="<?= htmlspecialchars($notification['link'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="d-flex w-100 justify-content-between align-items-start gap-3">
                                        <span class="fw-bold">
                                            <i class="bi <?= $typeIcon ?> me-2"></i><?= htmlspecialchars($typeLabel) ?>
                                        </span>
                                        <?php if (!empty($notification['created_at'])): ?>
                                            <small class="text-muted text-nowrap">
                                                <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($notification['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                                            </small>
                                        <?php endif; ?>
                                    </span>
                                    <span class="d-block mt-1"><?= htmlspecialchars((string) $notification['message'], ENT_QUOTES, 'UTF-8') ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-bell-fill text-primary me-2"></i>Détail de la notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p id="notificationDetailMessage" class="mb-3"></p>
                    <div class="small text-muted"><i class="bi bi-clock me-1"></i><span id="notificationDetailDate"></span></div>
                    <a id="notificationDetailLink" href="#" class="btn btn-primary rounded-pill mt-3 d-none">Voir les détails</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateUnreadBadge(delta) {
        const badge = document.querySelector('.notification-badge');
        if (!badge) return;
        const current = parseInt(badge.textContent, 10) || 0;
        const next = Math.max(0, current + delta);
        if (next === 0) {
            badge.remove();
        } else {
            badge.textContent = next;
        }
    }

    document.querySelectorAll('.notification-detail-button').forEach(function (button) {
        button.addEventListener('click', async function () {
            const wasUnread = button.classList.contains('unread');
            document.getElementById('notificationDetailMessage').textContent = button.dataset.message;
            document.getElementById('notificationDetailDate').textContent = button.dataset.date;
            const link = document.getElementById('notificationDetailLink');
            if (button.dataset.link) {
                link.href = button.dataset.link;
                link.classList.remove('d-none');
            } else {
                link.classList.add('d-none');
            }
            if (window.bootstrap) new bootstrap.Modal(document.getElementById('notificationDetailModal')).show();

            if (!wasUnread) return;

            try {
                const response = await fetch('/app/Model/notifications.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ notification_id: button.dataset.notificationId })
                });
                const data = await response.json();
                if (!response.ok || !data.success) return;

                button.classList.remove('unread', 'border-primary', 'fw-semibold');
                button.classList.add('border-light-subtle');
                updateUnreadBadge(-1);
            } catch (_) {
                // La notification reste affichée comme non lue si l'appel échoue.
            }
        });
    });
    document.getElementById('mark-all-read')?.addEventListener('click', async function () {
        const button = this;
        button.disabled = true;

        try {
            const response = await fetch('/app/Model/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ mark_all_read: true })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Impossible de mettre a jour les notifications.');
            }

            window.location.reload();
        } catch (error) {
            alert(error.message);
            button.disabled = false;
        }
    });
    </script>
    <?php require __DIR__ . '/app/inc/footer.php'; ?>
