<?php
/**
 * Zoho CRM Integration (API v3+) - Core PHP Example
 *
 * Description:
 * 1. Obtain an Access Token using a Refresh Token.
 * 2. Search for a Lead (by email) in Zoho CRM.
 * 3. If found, display info. If not found, create a new Lead.
 * 4. Display the result (or log any errors).
 *
 * Author: Kalyani Verma
 * Date: 2025-03-23
 */


// Zoho CRM API credentials
define('ZOHO_ACCESS_TOKEN', '1000.95af034cc6ac8a2b5df639b028e8ccbb.d77593090074ae7112a9c4bad8575d2d'); 
define('ZOHO_API_BASE_URL',  'https://www.zohoapis.com'); 
define('ZOHO_API_DATACENTER_URL',  'https://accounts.zoho.com'); 


/*----------------------------------------------------------------------
|   SETTINGS
|--------------------------------------------------------------------------
|   Decide which module you want to query or create a record in (Leads,
|   Contacts, etc.). Also set the search criteria key (such as Email).
|--------------------------------------------------------------------------
*/
$zohoModule       = 'Leads';  // Could also be 'Contacts', 'Deals', etc.
$searchField      = 'Email';  // Typically 'email', 'Phone', etc.
$searchValue      = 'kalyani@test.com'; // The value you want to find in Zoho
$newLeadData      = array(
    'Company'     => 'Yalla Tech Solutions LLC',
    'Last_Name'   => 'Verma',
    'First_Name'  => 'Kalyani',
    'Email'       => $searchValue,
    'Phone'       => '+1 999 888 7777'
);

/*----------------------------------------------------------------------
|   LOGGING
|--------------------------------------------------------------------------
|   Set a file path for logs. If you want to log errors or info, specify
|   a path with correct write permissions. E.g., /var/log/zoho_integration.log
|--------------------------------------------------------------------------
*/
$logFile = __DIR__ . '/zoho_integration.log';

/*----------------------------------------------------------------------
|   MAIN EXECUTION
|--------------------------------------------------------------------------
|   1. Get an Access Token using the Refresh Token.
|   2. Search for an existing record in Zoho CRM.
|   3. If not found, create a new record.
|   4. Print or log the result.
|--------------------------------------------------------------------------
*/
try {
    // 1) Obtain Access Token
      $accessToken = ZOHO_ACCESS_TOKEN;
     
    if (!$accessToken) {
        throw new Exception('Failed to retrieve an Access Token.');
    }

    // 2) Search existing record (by Email or other field)
    $existingRecord = searchRecord($accessToken, $zohoModule, $searchField, $searchValue);
    
    if ($existingRecord['status_code'] == 200 && !empty($existingRecord['response']['data'])) {
        // Found existing record
        echo "<pre> Record found in Zoho CRM:\n";
        print_r($existingRecord['response']['data']);
    } else {

            // Check for errors
        if ($existingRecord['status_code'] == 200 && !empty($existingRecord['response']['data'])) {
            // Found existing record
            echo "<pre> Record found in Zoho CRM:\n";
            print_r($existingRecord['response']['data']);

        } else { 
            // No existing record => create a new one
            echo "No existing record found. Creating a new one...\n";

            $createdRecord = createRecord($accessToken, $zohoModule, $newLeadData);
            if ($createdRecord === false) {
                throw new Exception('Failed to create new record in Zoho CRM.');
            }
            echo "<pre> New record created successfully:\n";
            print_r($createdRecord);
            
        }

    }
  
} catch (Exception $e) {
    // Log and display error
    logMessage($logFile, "ERROR: " . $e->getMessage());
    echo "ERROR: " . $e->getMessage();
    exit(1);
}

/**
 * Search for a record in Zoho CRM.
 * 
 * @param string $accessToken
 * @param string $module       
 * @param string $searchField  
 * @param string $searchValue
 */
function searchRecord($accessToken, $module, $searchField, $searchValue) {
    // Example endpoint: https://www.zohoapis.com/crm/v3/Leads/search?criteria=(Email:equals:test@example.com)

    // We must URL-encode the criteria
    $criteria = '(' . $searchField . ':equals:' .urlencode($searchValue). ')';
    $searchUrl = ZOHO_API_BASE_URL . "/crm/v3/{$module}/search?criteria={$criteria}";

    $headers = array(
        "Authorization: Zoho-oauthtoken $accessToken",
        "Content-Type: application/json"
    );

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => $searchUrl,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true
    )); 
    
      
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
    if (curl_errno($ch)) {
        logMessage($GLOBALS['logFile'], "cURL error (search): " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    curl_close($ch);

    
    $result = json_decode($response, true);
  
    
    return ['status_code' => $httpCode, 'response' => $result];
}

/**
 * Create a new record in Zoho CRM (e.g., a new Lead).
 *
 * @param string $accessToken
 * @param string $module   (e.g., 'Leads')
 * @param array  $data     Data to insert. Example for Leads: ['Last_Name' => 'Doe', 'Company' => 'XYZ Inc']
 * @return array|false     The created record on success, false on failure
 */
function createRecord($accessToken, $module, $data) {
    $url = ZOHO_API_BASE_URL . "/crm/v3/{$module}";

    // JSON in the form: {"data":[{ ...fields... }]}
    $payload = array(
        'data' => array($data)
    );
    $jsonPayload = json_encode($payload);

    $headers = array(
        "Authorization: Zoho-oauthtoken $accessToken",
        "Content-Type: application/json"
    );

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => $url,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $jsonPayload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true
    ));
    $response = curl_exec($ch);

     if (curl_errno($ch)) {
        logMessage($GLOBALS['logFile'], "cURL error (create): " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    curl_close($ch);

    $result = json_decode($response, true);

    // Check success
    if (isset($result['data'][0]['code']) && $result['data'][0]['code'] === 'SUCCESS') {
        // Return the created record details
        return $result['data'][0]['details'];
    } else {
        // Log error
        logMessage($GLOBALS['logFile'], "Zoho create record error: " . $response);
        return false;
    }
}

/**
 * Simple logger function: writes a timestamped message to a file
 *
 * @param string $filePath
 * @param string $message
 * @return void
 */
function logMessage($filePath, $message) {
    $time = date('Y-m-d H:i:s');
    $logLine = "[{$time}] {$message}\n";
    error_log($logLine, 3, $filePath);
}
