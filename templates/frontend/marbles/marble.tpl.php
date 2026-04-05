<div class="PagePanel">
    <h1><?= htmlspecialchars($marble->marble_name ?? '') ?></h1>

    <p>
        <strong>Size:</strong> <?= htmlspecialchars(ucfirst($marble->size)) ?><br>
        <strong>Color:</strong> <?= htmlspecialchars($marble->color) ?>
        <?php if ($marble->quantity > 1): ?>
            <br><strong>Quantity:</strong> <?= $marble->quantity ?>
        <?php endif; ?>
        <?php if ($marble->team_name): ?>
            <br><strong>Team:</strong> <?= htmlspecialchars($marble->team_name) ?>
        <?php endif; ?>
    </p>

    <?php if ($marble->description): ?>
        <h2>About</h2>
        <p><?= nl2br(htmlspecialchars($marble->description)) ?></p>
    <?php endif; ?>
</div>
