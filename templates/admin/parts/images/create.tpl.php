<?php
// File: /templates/admin/parts/images/create.tpl.php
date_default_timezone_set("Asia/Tokyo");
$date_prefix = strtolower(date("Y_M_d_"));
?>

<div class="PagePanel">
    <h3>Upload Images for <?= htmlspecialchars($part->name) ?></h3>

    <p><a href="/admin/parts/images/">← Back to Parts List</a></p>

    <form id="upload-form" method="POST" action="https://badmin.robnugen.com/bullet.php" enctype="multipart/form-data" target="_blank" autocomplete="on">
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000"/>

        <!-- Authentication -->
        <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
            <div>
                <label for="password">Password:</label>
                <input id="password" type="password" name="password"
                       autocomplete="current-password"
                       required style="padding: 5px; margin-left: 10px;"/>
            </div>
            <!-- Reveal this and JS at bottom if a device won't save password otherwise.  <div style="margin-top: 10px;">
                <label for="remember-password" style="font-size: 0.9em;">
                    <input type="checkbox" id="remember-password" style="margin-right: 5px;">
                    Remember password on this device
                </label>
            </div> -->
        </fieldset>

        <!-- Upload Settings -->
        <div style="margin-bottom: 20px; display: flex; gap: 20px; align-items: center;">
            <div>
                <label for="save_to">Category:</label>
                <select name="save_to" id="save_to" style="padding: 5px; margin-left: 10px;">
                    <option value="tmp">tmp (testing)</option>
                    <option value="mt3cons">MT3 construction/YYYY</option>
                    <option value="mt3parts" selected>MT3 parts/YYYY</option>
                </select>
            </div>

            <div>
                <label for="sub_dir">Sub directory:</label>
                <input type="text" name="sub_dir" id="sub_dir" placeholder="jan_30" style="padding: 5px; margin-left: 10px; width: 120px;"/>
            </div>

            <div>
                <label for="date_prefix">Date prefix:</label>
                <input type="text" name="date_prefix" id="date_prefix" value="<?= $date_prefix ?>" style="padding: 5px; margin-left: 10px; width: 150px;"/>
            </div>
        </div>

        <!-- Worker Selection -->
        <div style="margin-bottom: 30px;">
            <h3>Select Workers</h3>
            <p style="color: #666; font-size: 0.9em; margin-bottom: 15px;">Click to select workers associated with these photos. Selected workers show full names.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 8px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                <?php foreach ($workers as $worker): ?>
                    <label style="cursor: pointer;">
                        <input type="checkbox" name="workers[]" value="<?= $worker->worker_id ?>"
                               style="display: none;"
                               onchange="toggleWorkerButton(this)"
                               data-alias="<?= htmlspecialchars($worker->worker_alias) ?>"
                               data-name="<?= htmlspecialchars($worker->name ?: $worker->worker_alias) ?>">
                        <span class="worker-toggle-button" style="display: inline-block; padding: 6px 12px; background: #e9ecef; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9em; transition: all 0.2s;">
                            <?= htmlspecialchars($worker->worker_alias) ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Upload Slots -->
        <div style="margin-bottom: 20px;">
            <h3>Upload Images</h3>
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                    <div style="font-weight: bold; margin-bottom: 8px;">Image <?= $i ?>:</div>
                    <input type="file" name="pictures<?= $i ?>" accept="image/*" style="width: 100%; margin-bottom: 8px;"/>
                    <input type="text" name="image_name[<?= $i ?>]" placeholder="Image name" style="width: 100%; padding: 5px;"/>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Submit -->
        <div style="margin-top: 30px;">
            <button type="submit" style="padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer;">
                Upload Images to b.robnugen.com
            </button>
            <p style="margin-top: 10px; color: #666; font-size: 0.9em;">
                Note: This will open a new tab with the upload results. Copy the image URLs to add them to this part.
            </p>
        </div>
    </form>
</div>

<script>
// Password localStorage functionality
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    // const rememberCheckbox = document.getElementById('remember-password');
    const storageKey = 'mt3-upload-password';

    // Load saved password on page load
    const savedPassword = localStorage.getItem(storageKey);
    // if (savedPassword) {
        passwordField.value = savedPassword;
        // rememberCheckbox.checked = true;
    // }

/*    I hid all this after saving the PW in FF because my Android device refused to save it arghhhh!
    // Save/clear password when checkbox changes
    rememberCheckbox.addEventListener('change', function() {
        if (this.checked) {
            // Save current password
            if (passwordField.value) {
                localStorage.setItem(storageKey, passwordField.value);
            }
        } else {
            // Clear saved password
            localStorage.removeItem(storageKey);
        }
    });

    // Update localStorage when password changes (if remember is checked)
    passwordField.addEventListener('input', function() {
        if (rememberCheckbox.checked) {
            localStorage.setItem(storageKey, this.value);
        }
    });
*/
});

function toggleWorkerButton(checkbox) {
    const button = checkbox.nextElementSibling;
    const alias = checkbox.dataset.alias;
    const name = checkbox.dataset.name;

    if (checkbox.checked) {
        // Selected: show full name, blue style
        button.textContent = name;
        button.style.background = '#007bff';
        button.style.color = 'white';
        button.style.borderColor = '#007bff';
    } else {
        // Deselected: show alias, gray style
        button.textContent = alias;
        button.style.background = '#e9ecef';
        button.style.color = 'black';
        button.style.borderColor = '#ced4da';
    }
}
</script>
