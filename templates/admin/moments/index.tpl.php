<div class="PagePanel">
    <h1>All Moments</h1>
    <p><a href="/admin/moments/moment.php">Create New Moment</a></p>

    <div style="margin-bottom: 20px;">
        <form method="get" action="/admin/moments/" style="display: flex; align-items: center; gap: 10px;">
            <?php if ($take_id > 0): ?>
                <input type="hidden" name="take_id" value="<?= $take_id ?>">
            <?php endif; ?>
            <label for="filter">Filter moments:</label>
            <input type="text" id="filter" name="filter" value="<?= htmlspecialchars($filter) ?>"
                   placeholder="Search in notes..." style="padding: 5px; width: 250px;">
            <button type="submit" style="padding: 5px 10px;">Filter</button>
            <?php if (!empty($filter)): ?>
                <a href="/admin/moments/<?= $take_id > 0 ? '?take_id=' . $take_id : '' ?>" style="padding: 5px 10px; text-decoration: none; background: #f0f0f0; border: 1px solid #ccc;">Clear</a>
            <?php endif; ?>
        </form>
        <?php if (!empty($filter)): ?>
            <p style="margin-top: 10px; font-style: italic;">
                Showing results for: <strong><?= htmlspecialchars($filter) ?></strong>
                (<?= count($moments) ?> moment<?= count($moments) !== 1 ? 's' : '' ?> found)
            </p>
        <?php endif; ?>
    </div>

    <?php if ($take_id > 0): ?>
        <p>
            <a href="/admin/moments/?take_id=<?= $take_id - 1 ?>&filter=<?= urlencode($filter) ?>">Previous take</a> -
            <a href="/admin/moments/?take_id=<?= $take_id + 1 ?>&filter=<?= urlencode($filter) ?>">Next take</a>
        </p>
    <?php endif; // ($take_id > 0): ?>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Take ID</th>
                <th>Photo</th>
                <th>Notes</th>
                <th>Frames</th>
                <th>Date</th>
                <th>Phrase ID</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($moments as $moment): ?>
                <tr>
                    <td><a href="/admin/moments/?take_id=<?= $moment->take_id ?>"><?= $moment->take_id ?></a></td>
                    <td>
                        <?php if ($moment->photos[0]): ?>
                            <a href="<?= $moment->photos[0]->getUrl() ?>"><img src="<?= $moment->photos[0]->getThumbnailUrl() ?>" alt="Image preview" style="max-width: 100px;"></a>
                        <?php endif; ?>
                    </td>
                    <td><?= $moment->notes ?? '(no notes)' ?></td>
                    <td>
                        <?php if ($moment->frame_start || $moment->frame_end): ?>
                            <?= $moment->frame_start ?? '?' ?> -
                            <a href="/admin/moments/moment.php?take_id=<?= $moment->take_id ?>&frame_start=<?= $moment->frame_end ?>">
                                <?= $moment->frame_end ?? '?' ?>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($moment->moment_date ?? '') ?></td>
                    <td><?= htmlspecialchars((string)$moment->phrase_id ?? '') ?></td>
                    <td>
                        <a href="/admin/moments/moment.php?id=<?= $moment->moment_id ?>">
                            Edit
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($moments)): ?>
        <p>No moments found<?= !empty($filter) ? ' matching your filter' : '' ?>.</p>
    <?php endif; ?>

    <p><a href="/admin/moments/moment.php">Create New Moment</a></p>
</div>

