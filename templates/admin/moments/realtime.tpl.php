<style>
    .tokens-container {
        border: 1px solid #ddd;
        padding: 10px;
        margin-top: 10px;
        min-height: 40px;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        background-color: #f9f9f9;
        border-radius: 4px;
    }
    .build-a-phrase-container {
        border: 2px dashed #007bff;
        background-color: #f0f8ff;
    }
    .blue-background-class {
        background-color: #c8e6c9 !important;
        border: 1px solid #777;
    }
    .token-item {
        padding: 5px 10px;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: move;
        font-size: 0.9em;
        user-select: none;
    }
    .token-item:active {
        cursor: grabbing;
    }
    .token-permanent {
        border: 2px solid #000000 !important;
        font-weight: bold;
    }
    .phrase-builder-area {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .create-moment-btn {
        padding: 5px 10px;
        font-size: 0.9em;
        height: fit-content;
    }
</style>

<div class="PagePanel">
    <h1>Realtime Moments</h1>

    <div class="worker-grid" style="display: grid; grid-template-columns: 1fr; gap: 40px;">
        <?php foreach ($workers as $worker): ?>
            <div class="worker-card" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px;">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <?php if (!empty($worker->photos[0])): ?>
                        <img src="<?= htmlspecialchars($worker->photos[0]->getThumbnailUrl()) ?>" alt="<?= htmlspecialchars($worker->name) ?>" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 15px;">
                    <?php endif; ?>
                    <h3 style="margin: 0;"><?= htmlspecialchars($worker->name) ?></h3>
                </div>

                <!-- Recent Moments (for context) -->
                <div class="recent-moments" style="margin-top: 15px;">
                    <h4 style="margin-bottom: 5px; font-size: 1em; color: #333;">Recent Activity:</h4>
                    <?php if (!empty($worker->moments)): ?>
                        <ul style="font-size: 0.85em; padding-left: 20px; margin: 0; color: #555;">
                            <?php foreach ($worker->moments as $moment): ?>
                                <li>
                                    <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>"><?= $moment->moment_id ?></a>
                                    <?= htmlspecialchars($moment->notes) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="font-size: 0.85em; color: #888; font-style: italic;">No recent moments.</p>
                    <?php endif; ?>
                </div>

                <!-- Available Tokens -->
                <div class="tokens-section" style="margin-top: 15px;">
                    <h4 style="margin-bottom: 5px; font-size: 1em; color: #333;">Available Tokens:</h4>
                    <div id="available-tokens-<?= $worker->worker_id ?>" class="tokens-container available-tokens">
                        <?php if (!empty($worker->tokens)): ?>
                            <?php foreach ($worker->tokens as $token): ?>
                                <div class="token-item <?= $token->is_permanent ? 'token-permanent' : '' ?>" data-token-id="<?= $token->token_id ?>" title="Token ID: <?= $token->token_id ?>">
                                    <?= htmlspecialchars($token->token_string) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-size: 0.85em; color: #888; font-style: italic; margin: 0;">No available tokens.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Phrase Builder -->
                <div class="phrase-builder-section" style="margin-top: 15px;">
                    <h4 style="margin-bottom: 5px; font-size: 1em; color: #333;">Build-a-Phrase:</h4>
                    <div class="phrase-builder-area">
                        <div id="build-a-phrase-<?= $worker->worker_id ?>" class="tokens-container build-a-phrase-container" style="flex-grow: 1;">
                            <!-- Tokens will be dropped here -->
                        </div>
                        <button class="create-moment-btn" data-worker-id="<?= $worker->worker_id ?>">Create Moment</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- CDN for SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/admin/js/realtime-moments.js"></script>