# Zoho CRM API V3 Integration Guide

## Objective
Zoho CRM API Integration Task
Objective:
Write a PHP script that integrates with Zoho CRM (API v3 or above) to:
1. Search for an existing Enquiry (Lead or Contact).
2. If it exists, display the result.
3. If it does not exist, create a Lead in Zoho CRM and display the result.

## Prerequisites
- A Zoho CRM account
- API access enabled in Zoho CRM
- A registered OAuth client for authentication
- Postman or any API testing tool (optional for testing)
- PHP Programming language with an HTTP request library

## Step 1: Configure Zoho CRM API Credentials
### 1.1 Register Your Application
1. Log in to [Zoho API Console](https://api-console.zoho.com/).
2. Create a new client.
3. Choose the **Self Client** or any other type of client.
4. Note down the following in the text file:
   - **Client ID**
   - **Client Secret**
   - **Generate Scope with group of modules** 
   
   Example : 
   ```
   https://accounts.zoho.com/oauth/v2/token?grant_type=authorization_code&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET&code=YOUR_AUTHORIZATION_CODE
   ```
### Notes
You can visit this link for detailed [OAuth 2.0 for v3 APIs- An Overview](https://www.zoho.com/crm/developer/docs/api/v3/oauth-overview.html) 
Follow this link for more information - [Scopes](https://www.zoho.com/crm/developer/docs/api/v3/scopes.html)
   

### 1.2 Generate OAuth Tokens
1. Obtain the `code` parameter:
   ```
   https://accounts.zoho.com/oauth/v2/token?grant_type=authorization_code&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET&code=YOUR_AUTHORIZATION_CODE
   ```
2. Exchange `code` for an `access_token` and `refresh_token`:
   ```
   curl -X POST https://accounts.zoho.com/oauth/v2/token \
   -d "client_id=YOUR_CLIENT_ID" \
   -d "client_secret=YOUR_CLIENT_SECRET" \
   -d "code=YOUR_AUTHORIZATION_CODE" \
   -d "grant_type=authorization_code"
   ```
3. Store the `refresh_token` securely for generating future access tokens.

## Step 2: Search for an Existing Enquiry
To search for an existing Lead or Contact, use the `searchLead` API:
```bash
curl -X GET "https://www.zohoapis.com/crm/v3/Leads/search?criteria=(Email:equals:'user@example.com')" \
-H "Authorization: Zoho-oauthtoken YOUR_ACCESS_TOKEN"
```

## Step 3: Display or Create a Lead
### If a Lead Exists
Print the message and display the result from the API response.

### If a Lead Does Not Exist
Create a new Lead using the following API request:
```bash
curl -X POST "https://www.zohoapis.com/crm/v3/Leads" \
-H "Authorization: Zoho-oauthtoken YOUR_ACCESS_TOKEN" \
-H "Content-Type: application/json" \
-d '{
  "data": [
    {
      "Last_Name": "Abc",
      "First_Name": "Michal",
      "Email": "user@example.com",
      "Company": "Tech Solutions",
	  "Phone": "+1 999 888 7777"
    }
  ]
}'
```
### Response Handling and Error Handling in the log file.
- If successful, Print message and display the response.
- If an error occurs, log the error for debugging in "zoho_integration.log".

## Notes
- use the `refresh_token` to regenerate `access_token` when needed.
- The API base URL (`https://www.zohoapis.com/`) may change based on the Zoho domain as per your region.

You can visit the [Zoho CRM API V3 Documentation](https://www.zoho.com/crm/developer/docs/api/v3/) for more detailed documentation. 
