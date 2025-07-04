<div class="PagePanel">
    <h1><?= $status ? 'Edit Spiral Support Outer Placement' : 'Create Spiral Support Outer Placement' ?></h1>

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
            <select name="part_id">
                <option value="">-- Select a Part --</option>
<?php foreach ($possParts as $part): ?>
                <option value="<?= $part->part_id ?>" <?= ($status->part_id ?? '') == $part->part_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars("{$part->part_alias} - {$part->name}") ?>
                </option>
<?php endforeach; ?>
            </select>
        </label><br><br>

        <label>
            SSOP Label: <?= $readonly_if_edit ?><br>
            <input type="text" name="ssop_label" value="<?= htmlspecialchars($status->ssop_label ?? 'SSOP' . $prefill_ssop_mm) ?>" <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            SSOP (mm): <?= $readonly_if_edit ?><br>
            <input type="text" name="ssop_mm" value="<?= htmlspecialchars($status->ssop_mm ?? $prefill_ssop_mm) ?>"  <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            Height (original): <?= $readonly_if_edit ?><br>
            <input type="text" name="height_orig" value="<?= htmlspecialchars($status->height_orig ?? $prefill_height_best ?? '') ?>"  <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            Height (best-fit): <?= $readonly_if_edit ?><br>
            <input type="text" name="height_best" value="<?= htmlspecialchars($status->height_best ?? $prefill_height_best ?? '') ?>"  <?= $readonly_if_edit ?>>
        </label><br><br>

        <label>
            Height (now): <br>
            <input type="text" name="height_now" value="<?= htmlspecialchars($status->height_now ?? $prefill_height_best ?? '') ?>">
        </label><br><br>

        <button type="submit">Save</button>
    </form>
</div>
