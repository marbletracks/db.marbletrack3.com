<div class="PagePanel">
    <h1>All Moments</h1>
    <p><a href="/admin/moments/moment.php">Create New Moment</a></p>
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
                    <td><?= $moment->take_id ?></td>
                    <td>
                        <?php if ($moment->photos[0]): ?>
                            <a href="<?= $moment->photos[0]->getUrl() ?>"><img src="<?= $moment->photos[0]->getThumbnailUrl() ?>" alt="Image preview" style="max-width: 100px;"></a>
                        <?php endif; ?>
                    </td>
                    <td><?= $moment->notes ?? '(no notes)' ?></td>
                    <td>
                        <?php if ($moment->frame_start || $moment->frame_end): ?>
                            <?= $moment->frame_start ?? '?' ?> - <?= $moment->frame_end ?? '?' ?>
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
    <p><a href="/admin/moments/moment.php">Create New Moment</a></p>
</div>

