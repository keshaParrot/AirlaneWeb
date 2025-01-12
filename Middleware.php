<?php

function authMiddleware(string $jwtSecret): ?array
{
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    try {
        $decoded = json_decode(base64_decode(explode('.', $token)[1]), true);
        $signature = hash_hmac('sha256', explode('.', $token)[0] . '.' . explode('.', $token)[1], $jwtSecret, true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        if ($signatureEncoded !== explode('.', $token)[2]) {
            throw new Exception('Invalid signature.');
        }

        if (isset($decoded['exp']) && time() > $decoded['exp']) {
            throw new Exception('Token expired.');
        }

        return $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
        exit;
    }
}

function authorizeSuperuser(array $user): void
{
    if (empty($user['is_superuser']) || !$user['is_superuser']) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: Superuser access required']);
        exit;
    }
}

function authorizeOwner(int $currentUserId, int $resourceOwnerId): void
{
    if ($currentUserId !== $resourceOwnerId) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden: Access denied']);
        exit;
    }
}
