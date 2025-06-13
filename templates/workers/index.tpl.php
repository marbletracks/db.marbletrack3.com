<h1>Meet the Workers of Marble Track 3</h1>

<div class="worker-grid">
    <?php foreach ($workers as $worker): ?>
        <div class="worker-card">
            <h2><?= htmlspecialchars($worker->getName()) ?></h2>
            <?php if ($worker->getPhotoCode()): ?>
                <img src="https://d2f8m59m4mubfx.cloudfront.net/<?= htmlspecialchars($worker->getPhotoCode()) ?>.jpg" alt="<?= htmlspecialchars($worker->getName()) ?>" style="max-width: 100%; height: auto;">
            <?php endif; // worker->getPhotoCode ?>
            <p><?= nl2br(htmlspecialchars($worker->getDescription())) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .worker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .worker-card {
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }
</style>
