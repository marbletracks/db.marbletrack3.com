<div class="PagePanel">
    <h1>All Marbles</h1>

    <p><a href="/admin/marbles/marble.php">Add New Marble</a></p>

    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
        <thead>
            <tr>
                <th>Alias</th>
                <th>Name</th>
                <th>Team</th>
                <th>Size</th>
                <th>Color</th>
                <th>Qty</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($marbles)): ?>
            <tr><td colspan="7">No marbles yet. Time to find them!</td></tr>
        <?php else: ?>
            <?php foreach ($marbles as $marble): ?>
            <tr>
                <td><?= htmlspecialchars($marble->marble_alias) ?></td>
                <td><?= htmlspecialchars($marble->marble_name) ?></td>
                <td><?= htmlspecialchars($marble->team_name ?? '') ?></td>
                <td><?= htmlspecialchars($marble->size) ?></td>
                <td><?= htmlspecialchars($marble->color) ?></td>
                <td><?= $marble->quantity ?></td>
                <td><a href="/admin/marbles/marble.php?id=<?= $marble->marble_id ?>">Edit</a></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <p><a href="/admin/marbles/marble.php">Add New Marble</a></p>
</div>
