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
                                    <?php if ($moment->take_id || $moment->frame_start || $moment->frame_end): ?>
                                        <span style="color: #007bff; font-weight: bold;">
                                            T: <?= $moment->take_id ?? '?' ?> <?= $moment->frame_start ?? '?' ?> - <?= $moment->frame_end ?? '?' ?>
                                        </span>
                                    <?php endif; ?>
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
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                        <h4 style="margin: 0; font-size: 1em; color: #333;">Available Tokens:</h4>
                        <button class="add-token-btn" data-worker-id="<?= $worker->worker_id ?>" style="padding: 3px 8px; font-size: 0.8em; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">Add Token</button>
                    </div>
                    <div id="available-tokens-<?= $worker->worker_id ?>" class="tokens-container available-tokens">
                        <?php if (!empty($worker->tokens)): ?>
                            <?php foreach ($worker->tokens as $token): ?>
                                <div class="token-item <?= $token->is_permanent ? 'token-permanent' : '' ?>" data-token-id="<?= $token->token_id ?>" data-token-date="<?= htmlspecialchars($token->token_date ?? '') ?>" title="Token ID: <?= $token->token_id ?>">
                                    <?= htmlspecialchars($token->token_string) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-size: 0.85em; color: #888; font-style: italic; margin: 0;">No available tokens.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Token Creation Form (hidden by default) -->
                    <div id="token-form-<?= $worker->worker_id ?>" class="token-form" style="display: none; margin-top: 10px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                        <h5 style="margin-top: 0;">Create New Token</h5>
                        <form class="token-creation-form" data-worker-id="<?= $worker->worker_id ?>">
                            <label style="display: block; margin-bottom: 10px;">
                                Token Text:<br>
                                <textarea name="token_string" rows="3" style="width: 100%; margin-top: 3px; resize: vertical;" placeholder="Enter token text..." required></textarea>
                            </label>
                            <label style="display: block; margin-bottom: 10px;">
                                Date:<br>
                                <input type="text" name="token_date" value="<?= date(format: "Y-m-d") ?>" style="width: 200px; margin-top: 3px;" placeholder="e.g., 2024-01-15">
                            </label>
                            <label style="display: block; margin-bottom: 15px;">
                                Color:<br>
                                <select name="token_color" style="margin-top: 3px;">
                                    <option value="Black">Black</option>
                                    <option value="Red">Red</option>
                                    <option value="Blue">Blue</option>
                                </select>
                            </label>
                            <div>
                                <button type="submit" style="padding: 5px 15px; background-color: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; margin-right: 10px;">Save Token</button>
                                <button type="button" class="cancel-token-btn" data-worker-id="<?= $worker->worker_id ?>" style="padding: 5px 15px; background-color: #6c757d; color: white; border: none; border-radius: 3px; cursor: pointer;">Cancel</button>
                            </div>
                        </form>
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

                <!-- Moment Editor (hidden by default) -->
                <div id="moment-editor-<?= $worker->worker_id ?>" class="moment-editor" style="display: none; margin-top: 20px; border-top: 2px solid #007bff; padding-top: 20px;">
                    <h4>Edit Moment Details</h4>
                    <form class="moment-editor-form" data-worker-id="<?= $worker->worker_id ?>">
                        <input type="hidden" name="token_ids">
                        <input type="hidden" name="phrase_string">
                        <label>
                            Notes:<br>
                            <textarea id="shortcodey" name="notes" class="shortcodey-textarea" rows="5" style="width: 100%;"></textarea>
                        </label>
                        <div class="perspective-fields" style="margin-top: 20px;"></div>

                        <div style="display: flex; gap: 20px; margin-top: 15px;">
                            <label>Take:
                                <select name="take_id" style="width: 150px;">
                                    <option value="">-- Select a Take --</option>
                                    <?php foreach ($takes as $take): ?>
                                        <option value="<?= $take->take_id ?>">
                                            <?= htmlspecialchars($take->take_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Frame Start: <input type="number" name="frame_start" style="width: 100px;"></label>
                            <label>Frame End: <input type="number" name="frame_end" style="width: 100px;"></label>
                            <label>Moment Date: <input type="date" name="moment_date"></label>
                        </div>

                        <div style="margin-top: 15px;">
                            <label>Image URL:
                                <input type="url" name="image_url" style="width: 100%; max-width: 500px;" placeholder="Paste image URL here (copied from worker photos above)">
                            </label>
                        </div>

                        <div style="margin-top: 20px;">
                            <button type="submit" class="save-moment-btn">Search for Moments</button>
                            <button type="button" class="create-new-moment-btn" style="display:none;">Create New Moment</button>
                            <button type="button" class="cancel-edit-btn">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Moment Search Results (hidden by default) -->
                <div id="moment-search-results-<?= $worker->worker_id ?>" class="moment-search-results" style="display: none; margin-top: 20px; border-top: 2px solid #6c757d; padding-top: 20px;">
                    <h4>Potential Existing Moments</h4>
                    <div class="results-container"></div>
                    <div style="margin-top: 15px;">
                        <button type="button" class="use-selected-moment-btn" disabled>Use Selected Moment &amp; Create Phrase</button>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
</div>

<link rel="stylesheet" href="/admin/css/autocomplete.css">
<script src="/admin/js/autocomplete.js" defer></script>
<!-- CDN for SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/admin/js/realtime-moments.js"></script>
