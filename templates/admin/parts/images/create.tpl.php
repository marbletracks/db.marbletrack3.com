<?php
// File: /templates/admin/parts/images/create.tpl.php
date_default_timezone_set("Asia/Tokyo");
$date_prefix = strtolower(date("Y_M_d_"));
?>

<div class="PagePanel">
    <h3>Upload Images for <?= htmlspecialchars($part->name) ?></h3>

    <p><a href="/admin/parts/images/">← Back to Parts List</a></p>

    <form id="upload-form" method="POST" action="https://badmin.robnugen.com/bullet.php" enctype="multipart/form-data" autocomplete="on">
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
            <input type="hidden" name="output" value="json"/>
            <button type="submit" id="upload-button" style="padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer;">
                Upload Images & Save to Database
            </button>
            <div id="upload-status" style="margin-top: 15px; display: none;">
                <div id="upload-progress" style="padding: 10px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 4px; color: #1976d2;">
                    <strong>Uploading...</strong> Please wait while your images are processed.
                </div>
            </div>
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
    // AJAX form submission
    const form = document.getElementById('upload-form');
    const uploadButton = document.getElementById('upload-button');
    const uploadStatus = document.getElementById('upload-status');
    const uploadProgress = document.getElementById('upload-progress');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get selected workers
        const selectedWorkers = Array.from(document.querySelectorAll('input[name="workers[]"]:checked'))
            .map(checkbox => parseInt(checkbox.value));

        // Check if any files are selected
        const fileInputs = form.querySelectorAll('input[type="file"]');
        const hasFiles = Array.from(fileInputs).some(input => input.files.length > 0);

        if (!hasFiles) {
            alert('Please select at least one image to upload.');
            return;
        }

        // Show progress, disable button
        uploadStatus.style.display = 'block';
        uploadButton.disabled = true;
        uploadButton.textContent = 'Uploading...';

        try {
            // Step 1: Upload to bullet.php
            const formData = new FormData(form);

            const uploadResponse = await fetch('https://badmin.robnugen.com/bullet.php', {
                method: 'POST',
                body: formData
            });

            if (!uploadResponse.ok) {
                throw new Error(`Upload failed: ${uploadResponse.status}`);
            }

            const imageUrls = await uploadResponse.json();

            if (!Array.isArray(imageUrls) || imageUrls.length === 0) {
                throw new Error('No images were uploaded successfully');
            }

            // Update progress
            uploadProgress.innerHTML = `<strong>Processing ${imageUrls.length} images...</strong> Saving to database.`;

            // Step 2: Save to local database
            const saveResponse = await fetch('/admin/parts/images/save_photos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    part_id: <?= $part->part_id ?>,
                    image_urls: imageUrls,
                    worker_ids: selectedWorkers
                })
            });

            if (!saveResponse.ok) {
                throw new Error(`Database save failed: ${saveResponse.status}`);
            }

            const saveResult = await saveResponse.json();

            if (!saveResult.success) {
                throw new Error(saveResult.error || 'Failed to save to database');
            }

            // Success!
            const workerText = selectedWorkers.length > 0
                ? ` and associated with ${selectedWorkers.length} worker${selectedWorkers.length !== 1 ? 's' : ''}`
                : '';

            uploadProgress.innerHTML = `<strong style="color: #2e7d32;">✅ Success!</strong> ${saveResult.data.photos_processed} image${saveResult.data.photos_processed !== 1 ? 's' : ''} uploaded${workerText}.`;
            uploadProgress.style.background = '#e8f5e8';
            uploadProgress.style.borderColor = '#4caf50';
            uploadProgress.style.color = '#2e7d32';

            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = '/admin/parts/images/';
            }, 3000);

        } catch (error) {
            console.error('Upload error:', error);
            uploadProgress.innerHTML = `<strong style="color: #c62828;">❌ Error:</strong> ${error.message}`;
            uploadProgress.style.background = '#ffebee';
            uploadProgress.style.borderColor = '#f44336';
            uploadProgress.style.color = '#c62828';

            // Re-enable button
            uploadButton.disabled = false;
            uploadButton.textContent = 'Upload Images & Save to Database';
        }
    });
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
