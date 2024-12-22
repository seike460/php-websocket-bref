<?php

require 'vendor/autoload.php';

use Aws\DynamoDb\DynamoDbClient;
use Aws\ApiGatewayManagementApi\ApiGatewayManagementApiClient;

// DynamoDB クライアント初期化
$ddb = new DynamoDbClient([
    'region' => 'ap-northeast-1',
    'version' => 'latest',
]);

return function ($event) use ($ddb) {
    $route = $event['requestContext']['routeKey'] ?? 'unknown';
    $connectionId = $event['requestContext']['connectionId'] ?? null;

    $tableName = 'WebSocketConnections';
    $apiGatewayEndpoint = "https://{$event['requestContext']['domainName']}/{$event['requestContext']['stage']}";
    $apiClient = new ApiGatewayManagementApiClient([
        'region' => 'ap-northeast-1',
        'version' => 'latest',
        'endpoint' => $apiGatewayEndpoint
    ]);

    try {
        switch ($route) {
            case '$connect':
                // 接続IDをDynamoDBに保存
                $ddb->putItem([
                    'TableName' => $tableName,
                    'Item' => ['connectionId' => ['S' => $connectionId]]
                ]);
                return ['statusCode' => 200, 'body' => "Connected"];

            case '$disconnect':
                // 切断時: DynamoDBから接続IDを削除
                $ddb->deleteItem([
                    'TableName' => $tableName,
                    'Key' => ['connectionId' => ['S' => $connectionId]]
                ]);
                return ['statusCode' => 200, 'body' => "Disconnected"];

            case 'message':
                // メッセージ受信: 全ての接続にブロードキャスト
                $body = json_decode($event['body'], true);
                $message = $body['message'] ?? 'No message';
                $response = $ddb->scan(['TableName' => $tableName]);
                foreach ($response['Items'] as $item) {
                    $targetConnectionId = $item['connectionId']['S'];
                    $apiClient->postToConnection([
                        'ConnectionId' => $targetConnectionId,
                        'Data' => json_encode(['type' => 'message', 'body' => "Client {$connectionId}: {$message}"])
                    ]);
                }
                return ['statusCode' => 200, 'body' => "Message broadcasted"];

            default:
                return ['statusCode' => 400, 'body' => "Unknown route"];
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        return ['statusCode' => 500, 'body' => "Error: " . $e->getMessage()];
    }
};
