<h1>Rides</h1>
<p>Choose your size, pick your ride, and roll!</p>

<div class="ride-filter">
    Filter: <strong>All</strong> |
    <a href="#" onclick="filterRides('large')">Large</a> |
    <a href="#" onclick="filterRides('medium')">Medium</a> |
    <a href="#" onclick="filterRides('small')">Small</a> |
    <a href="#" onclick="filterRides('all')">Show All</a>
</div>

<div class="ride-list">
    <?php foreach ($rides as $ride): ?>
        <div class="ride-card" data-size="<?= htmlspecialchars($ride->marble_size) ?>">
            <div class="ride-card-header">
                <h2>
                    <a href="/rides/<?= htmlspecialchars($ride->slug) ?>/">
                        <?= htmlspecialchars($ride->name) ?>
                    </a>
                </h2>
                <span class="size-badge size-<?= $ride->marble_size ?>">
                    <?= ucfirst($ride->marble_size) ?> only
                </span>
            </div>
            <p class="ride-tagline"><?= htmlspecialchars($ride->tagline) ?></p>
            <p class="ride-route">
                <?php
                $trackNames = array_map(fn($t) => htmlspecialchars($t->track_name), $ride->tracks);
                echo implode(' &rarr; ', $trackNames);
                ?>
            </p>
            <p class="ride-meta">
                <?= count($ride->tracks) ?> tracks
                <?php if (!$ride->is_complete): ?>
                    &middot; <em>Under construction</em>
                <?php endif; ?>
            </p>
        </div>
    <?php endforeach; ?>
</div>

<script>
function filterRides(size) {
    document.querySelectorAll('.ride-card').forEach(card => {
        card.style.display = (size === 'all' || card.dataset.size === size) ? '' : 'none';
    });
}
</script>

<style>
    .ride-filter { margin-bottom: 1.5em; }
    .ride-list { display: flex; flex-direction: column; gap: 1rem; }
    .ride-card {
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 1.25rem;
        border-radius: 8px;
        box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }
    .ride-card-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
    .ride-card-header h2 { margin: 0; }
    .ride-tagline { font-style: italic; color: #555; }
    .ride-route { color: #333; }
    .ride-meta { font-size: 0.9em; color: #777; }
    .size-badge {
        display: inline-block;
        padding: 0.25em 0.75em;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: bold;
        color: white;
    }
    .size-large { background-color: #c0392b; }
    .size-medium { background-color: #d4a017; }
    .size-small { background-color: #27ae60; }
</style>
