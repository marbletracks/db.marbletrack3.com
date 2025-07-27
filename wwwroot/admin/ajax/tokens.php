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
$request = new RobRequest();

$action = $request->getAction();

try {
    switch ($action) {
        case 'create_for_worker':
            $worker_id = $request->getInt('worker_id');
            $token_string = $request->getString('token_string');
            $token_date = $request->getString('token_date');
            $token_x_pos = $request->getInt('token_x_pos', 10);
            $token_y_pos = $request->getInt('token_y_pos', 10);
            $token_width = $request->getInt('token_width', 100);
            $token_height = $request->getInt('token_height', 30);
            $token_color = $request->getString('token_color', 'Black');

            $request->requireFields([
                'worker_id' => $worker_id > 0,
                'token_string' => $token_string !== ''
            ]);

            // Find or create a column for this worker
            $column_id = findOrCreateRealtimeColumn($columnsRepo, $worker_id);
            
            $tokenId = $tokensRepo->insert($column_id, $token_string, $token_date, $token_x_pos, $token_y_pos, $token_width, $token_height, $token_color);
            $token = $tokensRepo->findById($tokenId);

            $request->jsonSuccess(['token' => $token]);
            break;

        case 'create':
            $column_id = $request->getInt('column_id');
            $token_string = $request->getString('token_string');
            $token_date = $request->getString('token_date');
            $token_x_pos = $request->getInt('token_x_pos', 10);
            $token_y_pos = $request->getInt('token_y_pos', 10);
            $token_width = $request->getInt('token_width', 100);
            $token_height = $request->getInt('token_height', 30);
            $token_color = $request->getString('token_color', 'Black');

            $request->requireFields([
                'column_id' => $column_id > 0,
                'token_string' => $token_string !== ''
            ]);

            $tokenId = $tokensRepo->insert($column_id, $token_string, $token_date, $token_x_pos, $token_y_pos, $token_width, $token_height, $token_color);
            $token = $tokensRepo->findById($tokenId);

            $request->jsonSuccess(['token' => $token]);
            break;

        case 'update':
            $token_id = $request->getInt('token_id');
            $column_id = $request->getInt('column_id');
            $token_string = $request->getString('token_string');
            $token_date = $request->getString('token_date');
            $token_color = $request->getString('token_color', 'Black');

            $request->requireFields([
                'token_id' => $token_id > 0,
                'column_id' => $column_id > 0,
                'token_string' => $token_string !== ''
            ]);

            // Fetch existing token to get its position and size
            $existingToken = $tokensRepo->findById($token_id);
            if (!$existingToken) {
                $request->jsonError('Token not found');
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

            $request->jsonSuccess(['token' => $updatedToken]);
            break;

        case 'update_position':
            $token_id = $request->getInt('token_id');
            $x_pos = $request->getInt('x_pos');
            $y_pos = $request->getInt('y_pos');

            $request->requireFields([
                'token_id' => $token_id > 0
            ]);

            $tokensRepo->updatePosition($token_id, $x_pos, $y_pos);
            $request->jsonSuccess();
            break;

        case 'update_size':
            $token_id = $request->getInt('token_id');
            $width = $request->getInt('width', 100);
            $height = $request->getInt('height', 50);

            $request->requireFields([
                'token_id' => $token_id > 0
            ]);

            $tokensRepo->updateSize($token_id, $width, $height);
            $request->jsonSuccess();
            break;

        case 'delete':
            $token_id = $request->getInt('token_id');

            $request->requireFields([
                'token_id' => $token_id > 0
            ]);

            $tokensRepo->delete($token_id);
            $request->jsonSuccess();
            break;

        default:
            $request->jsonError('Invalid action');
    }
} catch (Exception $e) {
    $request->jsonError($e->getMessage());
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
