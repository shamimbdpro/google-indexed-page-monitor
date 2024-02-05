<?php

require 'vendor/autoload.php';

function getGoogleIndexedPages($domain) {
    // Set the path to your service account JSON key file
    $serviceAccountKeyFile = 'credential.json';

    // Set up the Google_Client with service account credentials
    $client = new Google_Client();
    $client->setAuthConfig($serviceAccountKeyFile);
    $client->addScope(Google_Service_Webmasters::WEBMASTERS_READONLY);

    // Create a new Google Service Webmasters instance
    $service = new Google_Service_Webmasters($client);

    // Get the list of sites associated with the authenticated user
    $sites = $service->sites->listSites();
    
    // Find the site in the list
    foreach ($sites->siteEntry as $siteEntry) {
        if ($siteEntry->siteUrl == $domain) {
            // Use siteUrl to query the Search Console API instead of siteId
            $siteUrl = $siteEntry->siteUrl;
            
            $searchAnalytics = $service->searchanalytics;
            $request = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
            
            // Set the start date to a distant date in the past
            $request->setStartDate('2000-01-01');
            
            // Set the end date to today's date
            $request->setEndDate(date('Y-m-d'));
            
            $request->setDimensions(['page']);

            // Query the Search Console API with siteUrl instead of siteId
            $result = $searchAnalytics->query($siteUrl, $request);

            // Display all indexed URLs
            if ($result->rows) {
                echo "<style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                        }
                        th, td {
                            padding: 10px;
                            border: 1px solid #ddd;
                            text-align: left;
                        }
                        th {
                            background-color: #f2f2f2;
                        }
                        strong {
                            color: #008000;
                        }
                    </style>";

                echo "<h2>Indexed Pages for $siteUrl</h2>";
                echo "<table>";
                echo "<tr><th>#</th><th>URL</th></tr>";
                $count = 1;
                foreach ($result->rows as $row) {
                    $pageUrl = isset($row->keys[0]) ? $row->keys[0] : 'N/A';
                    echo "<tr><td>$count</td><td>$pageUrl</td></tr>";
                    $count++;
                }
                echo "</table>";
            } else {
                echo "No indexed pages found for $siteUrl";
            }

            return;
        }
    }

    // If the site is not found
    echo "Site not found: $domain";
}

getGoogleIndexedPages('https://codepopular.com/');
