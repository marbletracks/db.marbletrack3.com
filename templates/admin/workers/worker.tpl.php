<div class="PagePanel">
    <h1><?= $worker ? 'Edit Worker' : 'Create Worker' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="Errors">
            <?php foreach ($errors as $err): ?>
                <p class="error"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label>
            Name:<br>
            <input type="text" name="name" value="<?= htmlspecialchars($worker->name ?? '') ?>">
        </label><br><br>
        <label>
            Alias:<br>
            <input type="text" name="worker_alias" value="<?= htmlspecialchars($worker->worker_alias ?? '') ?>">
        </label><br><br>
        <label>
            Description:<br>
            <textarea id="shortcodey" name="description" rows="15" cols="100"><?= htmlspecialchars($worker->description ?? '') ?></textarea>
            <div id="autocomplete"></div>
        </label><br><br>

        <?php if ($worker && !empty($worker->moments)): ?>
        <h2>Associated Moments</h2>
        <h4>(Oldest on top)</h4>
        <ul id="sortable-moments">
            <?php foreach ($worker->moments as $moment): ?>
                <li data-moment-id="<?= $moment->moment_id ?>" draggable="true">
                    <div class="drag-handle">⋮⋮</div>
                    <?php if ($moment->moment_date): ?>
                        (<?= htmlspecialchars($moment->moment_date) ?>)&nbsp;
                    <?php else: // ($moment->moment_date): ?>
                        (<?= "--/--/----" ?>)&nbsp;
                    <?php endif; // ($moment->moment_date): ?>
                    <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>"><?= htmlspecialchars($moment->notes) ?></a>
                    <?php if ($moment->frame_start || $moment->frame_end): ?>
                        &nbsp; (Frames: <?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?>)
                    <?php endif; ?>

                    <button type="button" class="remove-moment">Remove</button>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <input type="hidden" name="moment_ids" id="moment_ids_hidden">

        <label>
            Add Moment:<br>
            <select id="add-moment-select">
                <option value="">Select a moment to add</option>
                <?php
                $worker_moment_ids = $worker ? array_map(fn($m) => $m->moment_id, $worker->moments) : [];

                foreach ($prioritized_moments as $moment):
                    if (!in_array($moment->moment_id, $worker_moment_ids)): ?>
                        <option value="<?= $moment->moment_id ?>"
                                data-notes="<?= htmlspecialchars($moment->notes) ?>"
                                data-frame-start="<?= $moment->frame_start ?>"
                                data-frame-end="<?= $moment->frame_end ?>"
                                data-moment-date="<?= $moment->moment_date ?>">
                            <?= htmlspecialchars($moment->notes) ?>
                            <?php if ($moment->frame_start || $moment->frame_end): ?>
                                (Frames: <?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?>)
                            <?php endif; ?>
                            <?php if ($moment->moment_date): ?>
                                (<?= htmlspecialchars($moment->moment_date) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endif;
                endforeach;

                if (!empty($prioritized_moments) && !empty($other_moments)) {
                    echo '<option disabled>--------------------</option>';
                }

                foreach ($other_moments as $moment):
                    if (!in_array($moment->moment_id, $worker_moment_ids)): ?>
                        <option value="<?= $moment->moment_id ?>"
                                data-notes="<?= htmlspecialchars($moment->notes) ?>"
                                data-frame-start="<?= $moment->frame_start ?>"
                                data-frame-end="<?= $moment->frame_end ?>"
                                data-moment-date="<?= $moment->moment_date ?>">
                            <?= htmlspecialchars($moment->notes) ?>
                            <?php if ($moment->frame_start || $moment->frame_end): ?>
                                (Frames: <?= $moment->frame_start ?? '?' ?>-<?= $moment->frame_end ?? '?' ?>)
                            <?php endif; ?>
                            <?php if ($moment->moment_date): ?>
                                (<?= htmlspecialchars($moment->moment_date) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endif;
                endforeach;
                ?>
            </select>
        </label><br><br>

        <label>
            Image URLs:<br>
            <div id="image-url-fields">
                <?php if(!empty($worker->photos)):foreach ($worker->photos ?? [''] as $photo): ?>
                    <img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Image preview"><br>
                    <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>"><br>
                <?php endforeach; ?>
                <?php endif; ?>
                <!-- add empty row so we always have space -->
                <input type="text" size=130 name="image_urls[]" value=""><br>
            </div>
            <button type="button" onclick="addImageUrlField()">Add another</button>
        </label>

        <script>
            function addImageUrlField() {
                const div = document.getElementById('image-url-fields');
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'image_urls[]';
                div.appendChild(input);
                div.appendChild(document.createElement('br'));
            }
        </script>

        <button type="submit">Save</button>
    </form>
</div>
<link rel="stylesheet" href="/admin/css/autocomplete.css">
<script src="/admin/js/autocomplete.js" defer></script>
<link rel="stylesheet" href="/admin/css/sortable-moments.css">
<script src="/admin/js/sortable-moments.js" defer></script>

