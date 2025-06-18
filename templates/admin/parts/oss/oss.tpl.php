<?php
// File: templates/admin/parts/oss/oss.tpl.php
?>
<div class="PagePanel">
    <h1><?= $status ? 'Edit OSS Support' : 'Create OSS Support' ?></h1>

    <?php if (!empty($errors)): ?>
            <div class="Errors">
                <?php foreach ($errors as $err): ?>
                        <p class="error"><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
    <?php endif; ?>

    <form action="" method="post">
<?php $readonly_if_edit = isset($status) ? 'readonly' : ''; ?>
<?php if (isset($status)): ?>
    <label>
        OSS Status ID: <?= $readonly_if_edit ?><br>
        <input type="text" name="parts_oss_status_id" value="<?= htmlspecialchars($status->parts_oss_status_id) ?>" readonly>
    </label><br><br>
<?php endif; ?>

        <label>
            Part ID: <br>
            <input type="number" name="part_id" value="<?= htmlspecialchars($status->part_id ?? '') ?>">
        </label><br><br>

        <label>
            SSOP Label: <?= $readonly_if_edit ?><br>
            <input type="text" name="ssop_label" value="<?= htmlspecialchars($status->ssop_label ?? '') ?>" <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            SSOP (mm): <?= $readonly_if_edit ?><br>
            <input type="text" name="ssop_mm" value="<?= htmlspecialchars($status->ssop_mm ?? '') ?>"  <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            Height (original): <?= $readonly_if_edit ?><br>
            <input type="text" name="height_orig" value="<?= htmlspecialchars($status->height_orig ?? '') ?>"  <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            Height (best-fit): <?= $readonly_if_edit ?><br>
            <input type="text" name="height_best" value="<?= htmlspecialchars($status->height_best ?? '') ?>"  <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            Height (now): <br>
            <input type="text" name="height_now" value="<?= htmlspecialchars($status->height_now ?? '') ?>">
        </label><br><br>

        <button type="submit">Save</button>
    </form>
</div>
