<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <style>
        * {
            font-family: Helvetica, Arial, sans-serif
          }
        .online {
            color: green;
            font-weight: bold;
        }
        .offline {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Landing page</h2>
    <ul>
        <?php
        // Array of URLs to check
        $urls = [
            "http://www.google.com",
            "http://example.com:7878",
            "http://123.123.123.123:8080",
            "http://192.168.0.22:9117/path",
            // Add more URLs as needed
        ];

        // Function to check if a URL is online and fetch the page title using cURL
        function checkUrl($url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout after 15 seconds
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL verification
            curl_setopt($ch, CURLOPT_HEADER, true); // Include header in the output
            curl_setopt($ch, CURLOPT_NOBODY, false); // Fetch the body to extract the title
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $title = "No Title Found";

            // Extract title from the HTML content if the status is OK
            if ($http_code === 200) {
                if (preg_match("/<title>(.*?)<\/title>/i", $response, $matches)) {
                    $title = $matches[1];
                }
            }

            curl_close($ch);

            return [$http_code === 200, $title];
        }

        // Loop through each URL and check its status
        foreach ($urls as $url) {
            list($is_online, $title) = checkUrl($url);
            $status = $is_online ? 'online' : 'offline';
            $status_text = ucfirst($status);

            echo "<li><strong>$title:</strong> <a href=\"$url\" target=\"_blank\">$url</a> - <span class=\"$status\">$status_text</span></li>";
        }
        ?>
    </ul>
</body>
</html>