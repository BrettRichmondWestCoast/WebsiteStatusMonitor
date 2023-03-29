# Monitor Domains for SSL, HTTP Errors, and Send Email Notifications via SendLayer

This PHP script is designed to check a list of specified domains for SSL or other HTTP errors, and send email notifications to specified recipients using SendLayer API when any errors are detected. The script can be set to run at desired intervals using a cron job, providing a simple yet effective way to monitor the health of your domains.

## Features:

1. Monitors specified domains for SSL and HTTP errors, including common error codes like 404, 500, etc.
2. Sends email notifications to specified recipients using the SendLayer API when any errors are detected.
3. Can be set to run at desired intervals using a cron job, allowing users to customize the monitoring frequency.

## How to use:

1. Configure the list of domains to monitor by modifying the `$domains` array in the script.
2. Set the email recipients, sender name, and sender email by updating the `$emailRecipients`, `$emailSenderName`, and `$emailSender` variables.
3. Add your SendLayer API key to the `$sendLayerApiKey` variable.
4. Customize the list of HTTP error codes to monitor by modifying the `$errorCodes` array, if necessary.
5. Set up a cron job to execute the script at your desired intervals (e.g., every 5 minutes).

Example of a cron job to run the script every 5 minutes:

*/5 * * * * /usr/bin/php /path/to/your/script.php

Please ensure that the path to PHP and the script file are correct for your server.

With this script in place, you can receive email notifications whenever any SSL or HTTP errors are detected on your specified domains, helping you stay on top of your website's health and performance.
