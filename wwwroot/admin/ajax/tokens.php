<?php

// File: /wwwroot/admin/ajax/tokens.php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

use Database\TokensRepository;
use Database\ColumnsRepository;

$tokensRepo = new TokensRepository($mla_database);
$columnsRepo = new ColumnsRepository($mla_database);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'create') {
                $column_id = (int) ($_POST['column_id'] ?? 0);
                $token_string = trim($_POST['token_string'] ?? '');
                $token_date = trim($_POST['token_date'] ?? '');
                $token_x_pos = (int) ($_POST['token_x_pos'] ?? 0);
                $token_y_pos = (int) ($_POST['token_y_pos'] ?? 0);
                $token_width = (int) ($_POST['token_width'] ?? 100);
                $token_height = (int) ($_POST['token_height'] ?? 50);
                $token_color = $_POST['token_color'] ?? 'Black';

                if ($column_id === 0) {
                    throw new Exception('Column ID is required');
                }
                if ($token_string === '') {
                    throw new Exception('Token text is required');
                }

                // Verify column exists
                $column = $columnsRepo->findById($column_id);
                if (!$column) {
                    throw new Exception('Column not found');
                }

                $token_id = $tokensRepo->insert(
                    $column_id,
                    $token_string,
                    $token_date,
                    $token_x_pos,
                    $token_y_pos,
                    $token_width,
                    $token_height,
                    $token_color
                );

                $token = $tokensRepo->findById($token_id);
                echo json_encode(['success' => true, 'token' => $token]);
            }
            break;

        case 'PUT':
            parse_str(file_get_contents('php://input'), $data);
            $token_id = (int) ($_GET['id'] ?? 0);
            
            if ($token_id === 0) {
                throw new Exception('Token ID is required');
            }

            $token = $tokensRepo->findById($token_id);
            if (!$token) {
                throw new Exception('Token not found');
            }

            $token_string = trim($data['token_string'] ?? $token->token_string);
            $token_date = trim($data['token_date'] ?? $token->token_date);
            $token_x_pos = (int) ($data['token_x_pos'] ?? $token->token_x_pos);
            $token_y_pos = (int) ($data['token_y_pos'] ?? $token->token_y_pos);
            $token_width = (int) ($data['token_width'] ?? $token->token_width);
            $token_height = (int) ($data['token_height'] ?? $token->token_height);
            $token_color = $data['token_color'] ?? $token->token_color;

            $tokensRepo->update(
                $token_id,
                $token_string,
                $token_date,
                $token_x_pos,
                $token_y_pos,
                $token_width,
                $token_height,
                $token_color
            );

            $updatedToken = $tokensRepo->findById($token_id);
            echo json_encode(['success' => true, 'token' => $updatedToken]);
            break;

        case 'DELETE':
            $token_id = (int) ($_GET['id'] ?? 0);
            
            if ($token_id === 0) {
                throw new Exception('Token ID is required');
            }

            $token = $tokensRepo->findById($token_id);
            if (!$token) {
                throw new Exception('Token not found');
            }

            $tokensRepo->delete($token_id);
            echo json_encode(['success' => true]);
            break;

        case 'GET':
            if ($action === 'list') {
                $column_id = (int) ($_GET['column_id'] ?? 0);
                
                if ($column_id === 0) {
                    throw new Exception('Column ID is required');
                }

                $tokens = $tokensRepo->findByColumnId($column_id);
                echo json_encode(['success' => true, 'tokens' => $tokens]);
            } elseif ($action === 'get') {
                $token_id = (int) ($_GET['id'] ?? 0);
                
                if ($token_id === 0) {
                    throw new Exception('Token ID is required');
                }

                $token = $tokensRepo->findById($token_id);
                if (!$token) {
                    throw new Exception('Token not found');
                }

                echo json_encode(['success' => true, 'token' => $token]);
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}