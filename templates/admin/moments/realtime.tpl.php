<?php
// File: /templates/admin/moments/realtime.tpl.php
?>
<div class="PagePanel">
    <h1>Realtime Moments</h1>

    <div class="worker-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
        <?php foreach ($workers as $worker): ?>
            <div class="worker-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <?php if (!empty($worker->photos[0])): ?>
                        <img src="<?= htmlspecialchars($worker->photos[0]->getThumbnailUrl()) ?>" alt="<?= htmlspecialchars($worker->name) ?>" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 15px;">
                    <?php endif; ?>
                    <h3 style="margin: 0;"><?= htmlspecialchars($worker->name) ?></h3>
                </div>
                <p style="font-size: 0.9em; color: #666;">
                    Alias: <?= htmlspecialchars($worker->worker_alias ?? '—') ?>
                </p>
                
                <?php /* Future content will go here */ ?>

            </div>
        <?php endforeach; ?>
    </div>
</div>
