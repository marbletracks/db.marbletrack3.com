<?php
// File: templates/admin/parts/oss/index.tpl.php
?>
<div class="PagePanel">
    <h1>Outer Spiral Supports</h1>
    <p><a href="/admin/parts/oss/oss.php">Add New Support</a></p>
    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>SSOP Label</th>
            <th>SSOP (mm)</th>
            <th>Height (orig)</th>
            <th>Height (best)</th>
            <th>Height (now)</th>
            <th>Last Updated</th>
            <th>Action</th>
        </tr>
        <?php foreach ($ossParts as $oss): ?>
            <tr>
                <td><?= htmlspecialchars($oss->parts_oss_status_id) ?></td>
                <td><?= htmlspecialchars($oss->ssop_label) ?></td>
                <td><?= htmlspecialchars($oss->ssop_mm) ?></td>
                <td><?= htmlspecialchars($oss->height_orig) ?></td>
                <td><?= htmlspecialchars($oss->height_best) ?></td>
                <td><?= htmlspecialchars($oss->height_now ?? '') ?></td>
                <td><?= htmlspecialchars($oss->last_updated) ?></td>
                <td><a href="/admin/parts/oss/oss.php?id=<?= htmlspecialchars($oss->parts_oss_status_id) ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>