<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */
    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */
    'projects' => [
        'app' => [
            /*
             * ------------------------------------------------------------------------
             * Credentials / Service Account
             * ------------------------------------------------------------------------
             *
             * In order to access a Firebase project and its related services using a
             * server SDK, requests must be authenticated. For server-to-server
             * communication this is done with a Service Account.
             *
             * If you don't already have generated a Service Account, you can do so by
             * following the instructions from the official documentation pages at
             *
             * https://firebase.google.com/docs/admin/setup#initialize_the_sdk
             *
             * Once you have downloaded the Service Account JSON file, you can use it
             * to configure the package.
             *
             * If you don't provide credentials, the Firebase Admin SDK will try to
             * auto-discover them
             *
             * - by checking the environment variable FIREBASE_CREDENTIALS
             * - by checking the environment variable GOOGLE_APPLICATION_CREDENTIALS
             * - by trying to find Google's well known file
             * - by checking if the application is running on GCE/GCP
             *
             * If no credentials file can be found, an exception will be thrown the
             * first time you try to access a component of the Firebase Admin SDK.
             *
             */
            'credentials' => [
                'file' => env('FIREBASE_CREDENTIALS', '{"type": "service_account","project_id": "arbo-a6b5e","private_key_id": "66df527a5b41e58cbb17d5cf3d434c138294d416","private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQC6Ji184zmYBiqf\nNN/j+Dn4OPNBtEBviSFY31xFt2a1SBhD4d0ADAiL3CNAPukqBVMvsHwlErffUzMc\nODwho0/ZWTLvZaA2lIQt+WzqJx8H753H3I7Mv63sV0Lw7Lgxz0uVgvKWtM2rv6/K\nyPziZ8MjSC2ALTyfA8T3hJZafPAsTLufk0jL96V4zrKaSzz0xmkrf989t60PJNtP\nXLISkA80jGIHMIr4eGBDgtGhh0L4RudXXXFXulNdsL35BPLLZ4B/dwr5STBczMSb\nabKXQuu6v58tTRzRJbRVR7Y6TrCfpg7i4pFJlBtMwfVjdQjT98W8fCPadI12C3Ax\nE1nt6T7tAgMBAAECggEAC9nI3RHJNZ604XBYDZcct6sGf4kWbhNcmmAT64NOyF03\n4EB1lZ5uf9rqqpkmtmxi3J8fdCvCJXdSQmppF5oiR/vIBJojbj4bwSKHNsv5S4PL\nd3EY7TuJusleY2CqpE8maHUO9R27F0NLkX0krlQ9RdZ/QREAMj5m8HwSJ14bWzKG\nPmGclJQ066dF5J/nkBY0RORAdnx5j5v/wyfehFK0duszJigko4KIfvP83S6WduW8\nbjVdK/is2wbb9kP1m/axp1c781+XyADyKSGjc8EVs/L8BqfWbY4nESH5j42Zzdbn\nHPMJmVIXNXSTha4EUjVLwr25ViP/HbIwf6IAhDiSEQKBgQD/hM6H9qiM9Lv4Yu1n\nHs0xKVplCcrSmD8BeLnOGmkgfEwPPZGlInRTylx1l84IszwuM+o6yAzduOgQmOy8\nxNzCKEQ2K97jQdVDcA69xrcNvcEL3MbGihNGprfjtV5J5UlWtTIzqNtlN9AfLjRz\nuj53HOzdrGoV754D/sCN/xVd8QKBgQC6f+z9sD8gn6waifdu2Orqehl9p6pG/GDG\nFklzcVXeb96jPIagxLe8EJEGogQ6gC2xPfgfFi7nhtP/mjxjnn3CV5LWmxlkN0jq\nj9s4OQVji/Y/ZjFCY5FW6n2OeMfCsQ3FLhgG2I+RrbFV9F0wwFlcXXpwO8uXEQgA\nas2gL70kvQKBgFXoPs/znAOYHMKL8Cllb7OBpcSmoCxhx30lK8Mhmgqz/5Z4KsmM\nZfPt61wV582BBVC7X5rXu4uoKU27PIzS2y3j/9r+sPdTIPKFcE9Zyh2ymH72gVYr\nAgQU9Wp3hfXuQtQGI5S+xtSnCTAShswJ6AqADRsSZrBtWYEaW37iLjrhAoGAcrmd\nFNXlj3EJ0u2KG1Mu93ySz7xjP/WiplgxaOWQOBxDLdFe0+kPSY47WIQz67TL5ttD\nFgR0aBKFuRetDG8D15g9iOyyKvbjUP+bkDNrgDgqDAgWR0uurXPkNs9PuxFlciWP\nvC5d6vSZQVHoPcQldG9AkWgHLm/Yp0EMKv0S8lUCgYAYPIm/rkJkF14wbBgcc2QQ\nptnpkmhjxGg281siAV+NtEcgMeJxtna7HzOVOnRMZWbHDfWPsTNR/aUh772BO4cu\ncoozk8j2ObkPbwYjZrpD2mTCY5rcnoV4yf5+fCes4yncvgwZnwcJ6h0tsxDAmU63\nNNJmIl+UXezQe4XZIWOtMg==\n-----END PRIVATE KEY-----\n","client_email": "firebase-adminsdk-gjnj6@arbo-a6b5e.iam.gserviceaccount.com","client_id": "102804993040872084945","auth_uri": "https://accounts.google.com/o/oauth2/auth","token_uri": "https://oauth2.googleapis.com/token","auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs","client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-gjnj6%40arbo-a6b5e.iam.gserviceaccount.com"}'),

                /*
                 * If you want to prevent the auto discovery of credentials, set the
                 * following parameter to false. If you disable it, you must
                 * provide a credentials file.
                 */
                'auto_discovery' => true,
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Auth Component
             * ------------------------------------------------------------------------
             */

            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             */

            'database' => [
                /*
                 * In most of the cases the project ID defined in the credentials file
                 * determines the URL of your project's Realtime Database. If the
                 * connection to the Realtime Database fails, you can override
                 * its URL with the value you see at
                 *
                 * https://console.firebase.google.com/u/1/project/_/database
                 *
                 * Please make sure that you use a full URL like, for example,
                 * https://my-project-id.firebaseio.com
                 */
                'url' => env('FIREBASE_DATABASE_URL'),

                /*
                 * As a best practice, a service should have access to only the resources it needs.
                 * To get more fine-grained control over the resources a Firebase app instance can access,
                 * use a unique identifier in your Security Rules to represent your service.
                 *
                 * https://firebase.google.com/docs/database/admin/start#authenticate-with-limited-privileges
                 */
                // 'auth_variable_override' => [
                //     'uid' => 'my-service-worker'
                // ],
            ],

            'dynamic_links' => [
                /*
                 * Dynamic links can be built with any URL prefix registered on
                 *
                 * https://console.firebase.google.com/u/1/project/_/durablelinks/links/
                 *
                 * You can define one of those domains as the default for new Dynamic
                 * Links created within your project.
                 *
                 * The value must be a valid domain, for example,
                 * https://example.page.link
                 */
                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             */

            'storage' => [
                /*
                 * Your project's default storage bucket usually uses the project ID
                 * as its name. If you have multiple storage buckets and want to
                 * use another one as the default for your application, you can
                 * override it here.
                 */

                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             *
             * The Firebase Admin SDK can cache some data returned from the Firebase
             * API, for example Google's public keys used to verify ID tokens.
             *
             */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             *
             * Enable logging of HTTP interaction for insights and/or debugging.
             *
             * Log channels are defined in config/logging.php
             *
             * Successful HTTP messages are logged with the log level 'info'.
             * Failed HTTP messages are logged with the the log level 'notice'.
             *
             * Note: Using the same channel for simple and debug logs will result in
             * two entries per request and response.
             */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             *
             * Behavior of the HTTP Client performing the API requests
             */
            'http_client_options' => [
                /*
                 * Use a proxy that all API requests should be passed through.
                 * (default: none)
                 */
                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),

                /*
                 * Set the maximum amount of seconds (float) that can pass before
                 * a request is considered timed out
                 * (default: indefinitely)
                 */
                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Debug (deprecated)
             * ------------------------------------------------------------------------
             *
             * Enable debugging of HTTP requests made directly from the SDK.
             */
            'debug' => env('FIREBASE_ENABLE_DEBUG', false),
        ],
    ],
];
