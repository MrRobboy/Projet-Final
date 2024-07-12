<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;

$notificationServiceUrl = 'http://localhost:8000/notifications';
$billingServiceUrl = 'http://localhost:8001/invoices';
$contentServiceUrl = 'http://localhost:8002/content';

try {
    $httpClient = HttpClient::create();

    $response = $httpClient->request('POST', $notificationServiceUrl, [
        'json' => [
            'sujet' => 'Test Notification',
            'emailRecipient' => 'recipient@example.com',
            'message' => 'This is a test notification from index.php'
        ]
    ]);

    $statusCode = $response->getStatusCode();
    if ($statusCode === 201) {
        echo "Notification created successfully.\n";
    } else {
        echo "Failed to create notification. Status code: $statusCode\n";
    }

    $response = $httpClient->request('POST', $billingServiceUrl, [
        'json' => [
            'amount' => 100.00,
            'dueDate' => '2024-07-31',
            'customerEmail' => 'customer@example.com'
        ]
    ]);

    $statusCode = $response->getStatusCode();
    if ($statusCode === 201) {
        echo "Invoice created successfully.\n";
    } else {
        echo "Failed to create invoice. Status code: $statusCode\n";
    }

    $response = $httpClient->request('POST', $contentServiceUrl, [
        'json' => [
            'title' => 'Sample Title',
            'content' => 'Sample Content'
        ]
    ]);

    $statusCode = $response->getStatusCode();
    if ($statusCode === 201) {
        echo "Content created successfully.\n";
    } else {
        echo "Failed to create content. Status code: $statusCode\n";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
