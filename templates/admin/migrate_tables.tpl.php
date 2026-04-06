<h1>MarbleTrack3 Table Migration Dashboard</h1>

<a href="https://west1-phpmyadmin.dreamhost.com/signon.php?pma_servername=eich.robnugen.com" _target="_blank">Eich</a>
<script>
    function applyMigration(migration) {
        fetch('/admin/apply_migration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ migration: migration })
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                console.error("Failed to apply migration:", migration);
            }
        })
        .catch(error => {
            console.error("Error applying migration:", error);
        });
    }
</script>
<?php
if ($has_pending_migrations) {
        echo "<h3>Pending DB Migrations</h3><ul>";
        foreach ($pending_migrations as $migration) {
            echo "<li>$migration <button onclick=\"applyMigration('$migration')\">Apply</button></li>";
        }
        echo "</ul>";
    }
?>

<div class="PagePanel">
    <a href="/admin/">admin</a> <br />
</div>
