<?php

/**
 * CORS configuration pour React + cookies HttpOnly
 */

return [
  'paths' => ['api/*', 'auth/*'],
  'allowed_methods' => ['*'],
  'allowed_origins' => ['http://localhost:5173'], // React dev server
  'allowed_origins_patterns' => [],
  'allowed_headers' => ['*'],
  'exposed_headers' => [],
  'max_age' => 0,
  'supports_credentials' => true,
];