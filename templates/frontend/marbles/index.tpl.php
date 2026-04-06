<h1>Residents</h1>
<p>The marbles who call this track home.</p>

<div class="marble-grid">
    <?php foreach ($marbles as $marble): ?>
        <div class="marble-card">
            <h2>
                <a href="/marbles/<?= htmlspecialchars($marble->slug) ?>/">
                    <?= htmlspecialchars($marble->marble_name) ?>
                </a>
            </h2>
            <p>
                <strong><?= htmlspecialchars(ucfirst($marble->size)) ?></strong> /
                <?= htmlspecialchars($marble->color) ?>
                <?php if ($marble->quantity > 1): ?>
                    / qty: <?= $marble->quantity ?>
                <?php endif; ?>
            </p>
            <?php if ($marble->team_name): ?>
                <p><em><?= htmlspecialchars($marble->team_name) ?></em></p>
            <?php endif; ?>
            <?php if ($marble->description): ?>
                <p><?= nl2br(htmlspecialchars($marble->description)) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .marble-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .marble-card {
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }
</style>
