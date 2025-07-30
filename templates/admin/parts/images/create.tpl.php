<?php
// File: /templates/admin/parts/images/create.tpl.php
date_default_timezone_set("Asia/Tokyo");
$date_prefix = strtolower(date("Y_M_d_"));
?>

<div class="PagePanel">
    <h1>Upload Images for <?= htmlspecialchars($part->name) ?></h1>
    
    <p><a href="/admin/parts/images/">← Back to Parts List</a></p>
    
    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <strong>Part:</strong> <?= htmlspecialchars($part->name) ?> (<?= htmlspecialchars($part->part_alias) ?>)<br>
        <?php if (!empty($part->description)): ?>
            <strong>Description:</strong> <?= nl2br(htmlspecialchars($part->description)) ?>
        <?php endif; ?>
    </div>

    <form id="upload-form" method="POST" action="https://b.robnugen.com/bullet.php" enctype="multipart/form-data" target="_blank">
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000"/>
        
        <!-- Authentication -->
        <div style="margin-bottom: 20px;">
            <label for="password">Password:</label>
            <input id="password" type="password" name="password" required style="padding: 5px; margin-left: 10px;"/>
        </div>

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

        <!-- Worker Selection UI Options - Show me different styles -->
        <div style="margin-bottom: 30px; border: 2px solid #007bff; border-radius: 8px; padding: 20px;">
            <h3 style="margin-top: 0; color: #007bff;">Worker Selection (Choose UI Style)</h3>
            
            <!-- Option 1: Checkboxes in Grid -->
            <div style="margin-bottom: 25px;">
                <h4>Option 1: Checkbox Grid</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <?php foreach ($workers as $worker): ?>
                        <label style="display: flex; align-items: center; font-size: 0.9em;">
                            <input type="checkbox" name="workers[]" value="<?= $worker->worker_id ?>" style="margin-right: 5px;">
                            <?= htmlspecialchars($worker->name ?: $worker->worker_alias) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Option 2: Horizontal Checkboxes with Photos -->
            <div style="margin-bottom: 25px;">
                <h4>Option 2: Photo Checkboxes (Horizontal)</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <?php foreach ($workers as $worker): ?>
                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer; padding: 8px; border-radius: 5px; border: 2px solid transparent; transition: border-color 0.2s;">
                            <input type="checkbox" name="workers2[]" value="<?= $worker->worker_id ?>" style="margin-bottom: 5px;" onchange="this.parentElement.style.borderColor = this.checked ? '#007bff' : 'transparent'">
                            <?php if (!empty($worker->photos[0])): ?>
                                <img src="<?= htmlspecialchars($worker->photos[0]->getThumbnailUrl()) ?>" alt="<?= htmlspecialchars($worker->name) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-bottom: 3px;">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; background: #ddd; border-radius: 50%; margin-bottom: 3px;"></div>
                            <?php endif; ?>
                            <span style="font-size: 0.8em; text-align: center;"><?= htmlspecialchars($worker->name ?: $worker->worker_alias) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Option 3: Multi-select Dropdown -->
            <div style="margin-bottom: 25px;">
                <h4>Option 3: Multi-select Dropdown</h4>
                <select name="workers3[]" multiple size="8" style="width: 100%; padding: 5px; background: #f8f9fa;">
                    <?php foreach ($workers as $worker): ?>
                        <option value="<?= $worker->worker_id ?>">
                            <?= htmlspecialchars($worker->name ?: $worker->worker_alias) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: 0.9em; margin-top: 5px; color: #666;">Hold Ctrl/Cmd to select multiple workers</p>
            </div>

            <!-- Option 4: Toggle Buttons -->
            <div>
                <h4>Option 4: Toggle Buttons</h4>
                <div style="display: flex; flex-wrap: wrap; gap: 8px; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <?php foreach ($workers as $worker): ?>
                        <label style="cursor: pointer;">
                            <input type="checkbox" name="workers4[]" value="<?= $worker->worker_id ?>" style="display: none;" onchange="toggleButton(this)">
                            <span class="toggle-button" style="display: inline-block; padding: 6px 12px; background: #e9ecef; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.9em; transition: all 0.2s;">
                                <?= htmlspecialchars($worker->name ?: $worker->worker_alias) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Upload Slots -->
        <div style="margin-bottom: 20px;">
            <h3>Upload Images</h3>
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                    <div style="min-width: 60px; font-weight: bold;">Image <?= $i ?>:</div>
                    <input type="file" name="pictures<?= $i ?>" accept="image/*" style="flex: 1;"/>
                    <input type="text" name="image_name[<?= $i ?>]" placeholder="Image name" style="flex: 1; padding: 5px;"/>
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
function toggleButton(checkbox) {
    const button = checkbox.nextElementSibling;
    if (checkbox.checked) {
        button.style.background = '#007bff';
        button.style.color = 'white';
        button.style.borderColor = '#007bff';
    } else {
        button.style.background = '#e9ecef';
        button.style.color = 'black';
        button.style.borderColor = '#ced4da';
    }
}
</script>