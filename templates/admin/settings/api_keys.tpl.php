<h1>API Keys</h1>

<?php if (!empty($new_raw_key)): ?>
<div style="background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 4px;">
    <strong>New API Key Created</strong><br>
    Copy this key now. It will not be shown again.<br>
    <code id="raw-key" style="display: block; margin: 10px 0; padding: 10px; background: #fff; border: 1px solid #ccc; font-size: 1.1em; word-break: break-all;">
        <?= htmlspecialchars($new_raw_key) ?>
    </code>
    <button onclick="navigator.clipboard.writeText(document.getElementById('raw-key').textContent.trim()); this.textContent='Copied!';">
        Copy to Clipboard
    </button>
</div>
<?php endif; ?>

<h2>Generate New Key</h2>
<form method="post" action="/admin/settings/api_keys.php">
    <input type="hidden" name="action" value="generate">
    <label for="label">Label (optional):</label>
    <input type="text" name="label" id="label" placeholder="e.g. claude code, my script" style="width: 300px;">
    <button type="submit">Generate Key</button>
</form>

<h2>Your Keys</h2>
<?php if (empty($keys)): ?>
    <p>No API keys yet. Generate one above.</p>
<?php else: ?>
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">
    <thead>
        <tr>
            <th>Label</th>
            <th>Created</th>
            <th>Last Used</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($keys as $key): ?>
        <tr style="<?= $key['is_active'] ? '' : 'opacity: 0.5;' ?>">
            <td><?= htmlspecialchars($key['label'] ?: '(no label)') ?></td>
            <td><?= htmlspecialchars($key['created_at']) ?></td>
            <td><?= $key['last_used'] ? htmlspecialchars($key['last_used']) : 'Never' ?></td>
            <td><?= $key['is_active'] ? 'Active' : 'Revoked' ?></td>
            <td>
                <?php if ($key['is_active']): ?>
                <form method="post" action="/admin/settings/api_keys.php" style="display:inline;"
                      onsubmit="return confirm('Revoke this key? Any agents using it will stop working.');">
                    <input type="hidden" name="action" value="revoke">
                    <input type="hidden" name="key_id" value="<?= (int) $key['key_id'] ?>">
                    <button type="submit">Revoke</button>
                </form>
                <?php else: ?>
                    &mdash;
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
