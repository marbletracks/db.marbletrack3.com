<h1>Parts Catalog for Marble Track 3</h1>

<div class="part-grid">
    <?php foreach ($parts as $part): ?>
        <div class="part-card">
            <h2>
                <a href="/parts/<?= htmlspecialchars($part->slug) ?>/">
                    <?= htmlspecialchars($part->name) ?>
                </a>
            </h2>
            <?php if (!empty($part->photos) && $part->photos[0]): ?>
                <a href="/parts/<?= htmlspecialchars($part->slug) ?>/">
                    <img src="<?= $part->photos[0]->getThumbnailUrl() ?>" alt="<?= htmlspecialchars($part->name) ?>" style="max-width: 100%; height: auto;">
                </a>
            <?php endif; ?>
            <p><?= nl2br(htmlspecialchars($part->description)) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .part-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .part-card {
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }
</style>
