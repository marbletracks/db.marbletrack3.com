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

$tokensRepo = new TokensRepository($mla_database);

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
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
