<?php
declare(strict_types=1);

// File: /wwwroot/admin/ajax/tokens.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

use Database\TokensRepository;
use Database\ColumnsRepository;

$tokensRepo = new TokensRepository($mla_database);
$columnsRepo = new ColumnsRepository($mla_database);

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create_for_worker':
            $worker_id = (int) ($_POST['worker_id'] ?? 0);
            $token_string = trim($_POST['token_string'] ?? '');
            $token_date = trim($_POST['token_date'] ?? '');
            $token_x_pos = (int) ($_POST['token_x_pos'] ?? 10);
            $token_y_pos = (int) ($_POST['token_y_pos'] ?? 10);
            $token_width = (int) ($_POST['token_width'] ?? 100);
            $token_height = (int) ($_POST['token_height'] ?? 30);
            $token_color = trim($_POST['token_color'] ?? 'Black');

            if ($worker_id <= 0 || $token_string === '') {
                throw new Exception('Worker ID and token string are required');
            }

            // Find or create a column for this worker
            $column_id = findOrCreateRealtimeColumn($columnsRepo, $worker_id);
            
            $tokenId = $tokensRepo->insert($column_id, $token_string, $token_date, $token_x_pos, $token_y_pos, $token_width, $token_height, $token_color);
            $token = $tokensRepo->findById($tokenId);

            echo json_encode(['success' => true, 'token' => $token]);
            break;

        case 'create':
            $column_id = (int) ($_POST['column_id'] ?? 0);
            $token_string = trim($_POST['token_string'] ?? '');
            $token_date = trim($_POST['token_date'] ?? '');
            $token_x_pos = (int) ($_POST['token_x_pos'] ?? 10);
            $token_y_pos = (int) ($_POST['token_y_pos'] ?? 10);
            $token_width = (int) ($_POST['token_width'] ?? 100);
            $token_height = (int) ($_POST['token_height'] ?? 30);
            $token_color = trim($_POST['token_color'] ?? 'Black');

            if ($column_id <= 0 || $token_string === '') {
                throw new Exception('Column ID and token string are required');
            }

            $tokenId = $tokensRepo->insert($column_id, $token_string, $token_date, $token_x_pos, $token_y_pos, $token_width, $token_height, $token_color);
            $token = $tokensRepo->findById($tokenId);

            echo json_encode(['success' => true, 'token' => $token]);
            break;

        case 'update':
            $token_id = (int) ($_POST['token_id'] ?? 0);
            $column_id = (int) ($_POST['column_id'] ?? 0);
            $token_string = trim($_POST['token_string'] ?? '');
            $token_date = trim($_POST['token_date'] ?? '');
            $token_color = trim($_POST['token_color'] ?? 'Black');

            if ($token_id <= 0 || $column_id <= 0 || $token_string === '') {
                throw new Exception('Token ID, column ID and token string are required');
            }

            // Fetch existing token to get its position and size
            $existingToken = $tokensRepo->findById($token_id);
            if (!$existingToken) {
                throw new Exception('Token not found');
            }

            $tokensRepo->update(
                $token_id,
                $column_id,
                $token_string,
                $token_date,
                $existingToken->token_x_pos, // Keep existing position
                $existingToken->token_y_pos,
                $existingToken->token_width, // Keep existing size
                $existingToken->token_height,
                $token_color
            );

            $updatedToken = $tokensRepo->findById($token_id);

            echo json_encode(['success' => true, 'token' => $updatedToken]);
            break;

        case 'update_position':
            $token_id = (int) ($_POST['token_id'] ?? 0);
            $x_pos = (int) ($_POST['x_pos'] ?? 0);
            $y_pos = (int) ($_POST['y_pos'] ?? 0);

            if ($token_id <= 0) {
                throw new Exception('Token ID is required');
            }

            $tokensRepo->updatePosition($token_id, $x_pos, $y_pos);
            echo json_encode(['success' => true]);
            break;

        case 'update_size':
            $token_id = (int) ($_POST['token_id'] ?? 0);
            $width = (int) ($_POST['width'] ?? 100);
            $height = (int) ($_POST['height'] ?? 50);

            if ($token_id <= 0) {
                throw new Exception('Token ID is required');
            }

            $tokensRepo->updateSize($token_id, $width, $height);
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            $token_id = (int) ($_POST['token_id'] ?? 0);

            if ($token_id <= 0) {
                throw new Exception('Token ID is required');
            }

            $tokensRepo->delete($token_id);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Find or create a column for a worker to store realtime tokens
 * We'll use a special notebook/page for realtime columns
 */
function findOrCreateRealtimeColumn(ColumnsRepository $columnsRepo, int $worker_id): int {
    global $mla_database;
    
    // First, ensure we have a realtime notebook
    $realtime_notebook_id = findOrCreateRealtimeNotebook($mla_database);
    
    // Then, ensure we have a realtime page
    $realtime_page_id = findOrCreateRealtimePage($mla_database, $realtime_notebook_id);
    
    // Finally, find or create a column for this worker in the realtime page
    $sql = "SELECT column_id FROM columns WHERE worker_id = ? AND page_id = ? LIMIT 1";
    $results = $mla_database->fetchResults($sql, 'ii', [$worker_id, $realtime_page_id]);
    
    if ($results->numRows() > 0) {
        $results->setRow(0);
        return (int) $results->data['column_id'];
    }
    
    // Create a new realtime column for this worker
    $column_name = "Realtime Tokens";
    return $columnsRepo->insert($realtime_page_id, $worker_id, $column_name, 0);
}

function findOrCreateRealtimeNotebook($db): int {
    $sql = "SELECT notebook_id FROM notebooks WHERE title = 'Realtime Tokens' LIMIT 1";
    $results = $db->fetchResults($sql);
    
    if ($results->numRows() > 0) {
        $results->setRow(0);
        return (int) $results->data['notebook_id'];
    }
    
    // Create the realtime notebook
    $db->executeSQL(
        "INSERT INTO notebooks (title, created_at) VALUES ('Realtime Tokens', NOW())"
    );
    return $db->insertId();
}

function findOrCreateRealtimePage($db, int $notebook_id): int {
    $sql = "SELECT page_id FROM pages WHERE notebook_id = ? AND number = 'RT' LIMIT 1";
    $results = $db->fetchResults($sql, 'i', [$notebook_id]);
    
    if ($results->numRows() > 0) {
        $results->setRow(0);
        return (int) $results->data['page_id'];
    }
    
    // Create the realtime page
    $db->executeSQL(
        "INSERT INTO pages (notebook_id, number, created_at) VALUES (?, 'RT', NOW())",
        'i',
        [$notebook_id]
    );
    return $db->insertId();
}
