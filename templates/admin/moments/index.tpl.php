<div class="PagePanel">
    <h1>All Moments</h1>
    <p><a href="/admin/moments/moment.php">Create New Moment</a></p>

    <?php
    $missing = $missing ?? [];
    $sort = $sort ?? '';
    $total = $total ?? count($moments);
    $missingOptions = [
        'photo'        => 'Photo',
        'frame'        => 'Frame',
        'date'         => 'Date',
        'take'         => 'Take',
        'perspectives' => 'Perspectives',
    ];
    $hasAnyFilter = !empty($filter) || !empty($missing) || $sort !== '';
    ?>
    <div style="margin-bottom: 20px;">
        <form method="get" action="/admin/moments/" style="display: flex; flex-direction: column; gap: 10px;">
            <?php if ($take_id > 0): ?>
                <input type="hidden" name="take_id" value="<?= $take_id ?>">
            <?php endif; ?>
            <?php if ($sort !== ''): ?>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <?php endif; ?>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label for="filter">Filter moments:</label>
                <input type="text" id="filter" name="filter" value="<?= htmlspecialchars($filter) ?>"
                       placeholder="Search in notes..." style="padding: 5px; width: 250px;">
                <button type="submit" style="padding: 5px 10px;">Filter</button>
                <?php if ($hasAnyFilter): ?>
                    <a href="/admin/moments/<?= $take_id > 0 ? '?take_id=' . $take_id : '' ?>" style="padding: 5px 10px; text-decoration: none; background: #f0f0f0; border: 1px solid #ccc;">Clear</a>
                <?php endif; ?>
            </div>
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <strong>Missing:</strong>
                <?php foreach ($missingOptions as $key => $label): ?>
                    <label style="display: inline-flex; align-items: center; gap: 4px;">
                        <input type="checkbox" name="missing[]" value="<?= $key ?>"
                               <?= in_array($key, $missing, true) ? 'checked' : '' ?>
                               onchange="this.form.submit()">
                        <?= $label ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </form>
        <p style="margin-top: 10px; font-style: italic;">
            Showing <?= count($moments) ?> of <?= $total ?> moment<?= $total !== 1 ? 's' : '' ?>
            <?php if (!empty($filter)): ?>
                matching <strong><?= htmlspecialchars($filter) ?></strong>
            <?php endif; ?>
        </p>
    </div>

    <?php
    // Build a sortable column header. Toggles asc/desc on repeat clicks.
    $makeSortHeader = function (string $label, string $key) use ($take_id, $filter, $missing, $sort): string {
        $asc  = $key . '_asc';
        $desc = $key . '_desc';
        $nextSort = ($sort === $asc) ? $desc : $asc;
        $arrow = ($sort === $asc) ? ' ▲' : (($sort === $desc) ? ' ▼' : '');
        $q = [];
        if ($take_id > 0)        { $q['take_id'] = $take_id; }
        if (!empty($filter))     { $q['filter']  = $filter; }
        foreach ($missing as $m) { $q['missing[]'][] = $m; }
        $q['sort'] = $nextSort;
        $href = '/admin/moments/?' . http_build_query($q);
        return '<a href="' . htmlspecialchars($href) . '" style="text-decoration: none; color: inherit;">'
             . htmlspecialchars($label) . $arrow . '</a>';
    };
    ?>

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
                <th><?= $makeSortHeader('Date', 'date') ?></th>
                <th><?= $makeSortHeader('Perspectives', 'perspectives') ?></th>
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
                    <td style="text-align: right;"><?= (int) $moment->perspective_count ?></td>
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

