<?php
#!/usr/bin/env php
// Enable error reporting
error_reporting(E_ALL);

// Set the location of the error log file
ini_set('error_log', 'php_error_log.txt');

// Log errors to the specified file
ini_set('log_errors', '1');

// Do not display errors in the output
ini_set('display_errors', '0');
// List of domains to ping
$domains = [
    'https://expired.badssl.com/',
    //'https://untrusted-root.badssl.com/',


    // Add more domains as needed
];

// Email settings
$emailRecipients = [
    ['name' => 'Some User', 'email' => 'user@gmail.com'],
    ['name' => 'Some User', 'email' => 'user@gmail.com'],
    //['name' => 'Some User', 'email' => 'user@gmail.com'],
    //['name' => 'Some User', 'email' => 'user@gmail.com'],




    // Add more recipients as needed
];
$emailSenderName = 'UPTIME PING';
$emailSender = 'info@gmail.com';
$emailSubject = "Error detected on domain";
// SendLayer API settings
$sendLayerApiKey = '**************';
$sendLayerUrl = 'https://console.sendlayer.com/api/v1/email';

// List of HTTP error codes to monitor
$errorCodes = [400, 401, 403, 404, 500, 502, 503, 504];

// Log file settings
$logFile = 'error_log.txt';

// User agent settings
$userAgent = 'WebsiteStatusMonitor/1.0 (curl/' . curl_version()['version'] . '; PHP/' . phpversion() . '; YourHost)';
// Loop through each domain in the list
foreach ($domains as $domain) {
    // Initialize cURL and set options to check the domain
    $ch = curl_init($domain);

    // Set the curl options for a HEAD request
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 seconds

    // Add a custom User-Agent header
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

    // Execute the curl request
    curl_exec($ch);

    // Get response information and errors
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $ssl_result = curl_errno($ch); // Get SSL error number

    // Close the curl handle
    curl_close($ch);

    // If the status code matches any of the monitored error codes, there is an SSL error, or SSL certificate is invalid
    if (in_array($statusCode, $errorCodes) || !empty($curlError) || $ssl_result != 0) {
        // Prepare the email content
        $errorDetails = !empty($curlError) ? "SSL Error: $curlError" : "Status Code: $statusCode";
        $errorDetails = $ssl_result != 0 ? "Invalid SSL Certificate" : $errorDetails; // Add invalid SSL certificate error
        $emailBody = "An error was detected on domain: $domain ($errorDetails)";

        // Write the domain, status code, and timestamp to the log file
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] Domain: $domain | Status Code: $statusCode\n";
        $logDirectory = '/your/path/to/root/';
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, 0755, true);
        }
        $logFileHandle = fopen($logFile, 'a');
        fwrite($logFileHandle, $logEntry);
        fclose($logFileHandle);

        // Loop through each recipient
        foreach ($emailRecipients as $recipient) {
            // Prepare the payload for the SendLayer API
            $postData = [
                'From' => [
                    'name' => $emailSenderName,
                    'email' => $emailSender
                ],
                'To' => [
                    [
                        'name' => $recipient['name'],
                        'email' => $recipient['email']
                    ]
                ],
                'Subject' => $emailSubject,
                'ContentType' => 'HTML',
                'HTMLContent' => $emailBody,
                'PlainContent' => strip_tags($emailBody),
                'Tags' => [
                    'domain-check',
                    'error'
                ],
                'Headers' => [
                    'X-Mailer' => 'Domain Error Checker'
                ]
            ];

            // Initialize cURL and set options to send the email using the SendLayer API
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $sendLayerUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $sendLayerApiKey
                ]
            ]);

            // Execute the cURL request and store the response
            $response = curl_exec($curl);
            $error = curl_error($curl); // Get any curl error
            $curlInfo = curl_getinfo($curl); // Get curl request info for debugging
            curl_close($curl);

            // Uncomment the following lines to display the response, error, and request information for debugging purposes
            // echo 'Response: ' . $response . PHP_EOL;
            // echo 'Error: ' . $error . PHP_EOL;
            // print_r($curlInfo);
        }
    }
}
?>