<?php
// File: templates/admin/parts/oss/index.tpl.php
?>
<div class="PagePanel">

<div class="PagePanel">
    <h1>Best-Fit Tool</h1>
    <label>
        SSOP (mm): <input type="number" id="ssop_input" step="0.1">
    </label>
    <br>
    <label>
        Height (best) (mm): <input type="number" id="height_input" step="0.1">
    </label>
</div>

<script>
document.getElementById('ssop_input').addEventListener('input', function () {
    const ssop = parseFloat(this.value);
    if (isNaN(ssop)) return;
    fetch(`/admin/parts/oss/get_best_height.php?ssop_mm=${ssop}`)
        .then(r => r.json())
        .then(data => {
            if (data.height_mm !== undefined) {
                document.getElementById('height_input').value = data.height_mm;
            }
        });
});

document.getElementById('height_input').addEventListener('input', function () {
    const height = parseFloat(this.value);
    if (isNaN(height)) return;
    fetch(`/admin/parts/oss/get_best_ssop.php?height_mm=${height}`)
        .then(r => r.json())
        .then(data => {
            if (data.ssop_mm !== undefined) {
                document.getElementById('ssop_input').value = data.ssop_mm;
            }
        });
});
</script>


    <h1>Outer Spiral Supports</h1>
    <p><a href="/admin/parts/oss/oss.php">Add New Support</a></p>
    <p>SSOP = Spiral Support Offset Position</p>
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