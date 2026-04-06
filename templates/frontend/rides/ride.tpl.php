<h1><?= htmlspecialchars($ride->name) ?></h1>

<div class="ride-hero">
    <span class="size-badge size-<?= $ride->marble_size ?>">
        <?= ucfirst($ride->marble_size) ?> marbles only
    </span>
    <p class="ride-description"><?= nl2br(htmlspecialchars($ride->description)) ?></p>
</div>

<h2>Your journey:</h2>

<div class="ride-journey">
    <?php foreach ($ride->tracks as $i => $track): ?>
        <div class="journey-track">
            <div class="journey-number"><?= $track->sequence_order ?></div>
            <div class="journey-content">
                <h3><?= htmlspecialchars($track->track_name) ?></h3>
                <p><?= htmlspecialchars($track->experience_note) ?></p>
            </div>
        </div>
        <?php if ($i < count($ride->tracks) - 1): ?>
            <div class="journey-arrow">&darr;</div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php if (!$ride->is_complete): ?>
    <p class="ride-construction"><em>This ride is still under construction. Check back soon!</em></p>
<?php endif; ?>

<div class="ride-nav">
    <a href="/rides/">&larr; Back to all Rides</a>
</div>

<style>
    .ride-hero { margin-bottom: 2em; }
    .ride-description { font-size: 1.1em; line-height: 1.6; }
    .ride-journey { display: flex; flex-direction: column; gap: 0; }
    .journey-track {
        display: flex;
        gap: 1em;
        background: #f9f9f9;
        border: 1px solid #ddd;
        padding: 1em;
        border-radius: 8px;
    }
    .journey-number {
        font-size: 1.5em;
        font-weight: bold;
        color: #0366a8;
        min-width: 2em;
        text-align: center;
    }
    .journey-content h3 { margin: 0 0 0.25em 0; }
    .journey-content p { margin: 0; color: #555; }
    .journey-arrow { text-align: center; font-size: 1.5em; color: #999; padding: 0.25em 0; }
    .ride-construction { color: #c0392b; margin-top: 1.5em; }
    .ride-nav { margin-top: 2em; }
    .size-badge {
        display: inline-block;
        padding: 0.25em 0.75em;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: bold;
        color: white;
        margin-bottom: 0.5em;
    }
    .size-large { background-color: #c0392b; }
    .size-medium { background-color: #d4a017; }
    .size-small { background-color: #27ae60; }
</style>
