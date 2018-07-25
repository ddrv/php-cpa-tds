# Install

```
composer require ddrv/tds /path/to/project
```

# Add library
```php
<?php

require '/path/to/project/vendor/autoload.php';
```

# Using

```php
<?php

$body = file_get_contents('php://input');

$request = new \Ddrv\TDS\Core\Request($_SERVER, $_GET, $body, $_COOKIE);
$tds = new Ddrv\TDS\TDS();
$tds->click($request)->response()->out();
```

# Configure
```php
<?php

$config = new \Ddrv\TDS\Config\Config();

/*
 * ------------------------------------
 * Paths
 * ------------------------------------
 */
$config->path->links = '/path/to/links'; // path to compiled links classes. By default /path/to/project/data/links
$config->path->responses = '/path/to/responses'; // path to compiled responses classes. By default /path/to/project/data/responses
$config->path->tmp = '/path/to/temporary'; // path to compiled links classes. By default /tmp

/*
 * ------------------------------------
 * Key (define link id).
 * For example, link key = test
 * ------------------------------------
 */

/*
 * in path
 * http://site.com/link/test/some/path/positions
 *                 ^    ^    ^    ^    ^
 *                 |    |    |    |    |
 * position:       0    1    2    3    4
 * 
 */
$config->key->in = 'path';
$config->key->position = 1;

/*
 * in query
 * http//site.com/go.php?query1=test&query2=2&query3=some-value
 *                       ^           ^        ^
 *                       |           |        |
 * position:             query1      query2   query3
 */
$config->key->in = 'query';
$config->key->position = 'query1';

/*
 * in subdomain
 * http://tets.site.com/some/path
 */
$config->key->in = 'uri';
$config->key->position = 'host';
$config->key->pattern = '/^(?<link>[a-z](a-z0-9\-)?)\./ui';
$config->key->match = 'link';

/*
 * ------------------------------------
 * Traffic back (default response)
 * ------------------------------------
 */
$config->trafficBack->status = 301;
$config->trafficBack->headers = ['location: https://google.com', 'content-type: text/plain'];
$config->trafficBack->body = 'redirect to google';

$tds = new \Ddrv\TDS\TDS($config);

```


## Adding responses

```php
<?php
// save from json
$json = <<<JSON
{
  "key": "e404",
  "status": 404,
  "headers": [
    "content-type: text/plain"
  ],
  "body": "Not Found"
}
JSON;
$tds->storage()->response()->save($json);

// save from file
file_put_contents('/path/to/project/data/responses/e404.json', $json);
$tds->storage()->response()->save('/path/to/project/data/responses/e404.json');
```

## Adding links

```php
<?php

// save from json
$json = <<<JSON
{
  "key": "test",
  "responses": [
    "e404"
  ]
}
JSON;
$tds->storage()->link()->save($json);

// save from file
file_put_contents('/path/to/project/data/links/test.json', $json);
$tds->storage()->response()->save('/path/to/project/data/links/test.json');
```

## AB tests
```php
<?php
// response A
$responseA = <<<JSON
{
  "key": "response-a",
  "status": 301,
  "headers": [
    "location: https:\/\/site.com\/a",
    "content-type: text/plain"
  ],
  "body": "redirect to https:\/\/site.com\/a"
}
JSON;
// response B
$responseB = <<<JSON
{
  "key": "response-b",
  "status": 301,
  "headers": [
    "location: https:\/\/site.com\/b",
    "content-type: text/plain"
  ],
  "body": "redirect to https:\/\/site.com\/b"
}
JSON;
// test link
$link = <<<JSON
{
  "key": "test",
  "responses": [
    "response-a"
    "response-b"
  ]
}
JSON;
$tds->storage()->response()->save($responseA);
$tds->storage()->response()->save($responseB);
$tds->storage()->link()->save($link);

/*
 * you nueno save links and responses ONLY when changing
 */
```

## Traffic Rules

```php
<?php

/*
 * You can set any parameters of request and set rules by this parameters
 * By defaults, request have a this parameters:
 * 
 * header.{HEADER_NAME} = {HEADER_VALUE}
 * cookie.{COOKIE_NAME} = {COOKIE_VALUE}
 * ip = $_SERVER['REMOTE_ADDR']
 * uri.scheme = https OR http
 * uri.host = {HOST_OF_REQUEST} (for example, site.com)
 * uri.port = {PORT_OF_REQUEST} (for example, 8081)
 * uri.path = {PATH_OF_REQUEST} (for example, /link/go.php)
 * uri.query = {QUERY_STRING_OF_REQUEST} (for.example, link=test&parameter=value)
 * uri = {URI_FULL_STRING} (for example, http://site.com:8081/link/go.php?link=test&parameter=value)
 * query.{QUERY_PARAM_NAME} = {QUERY_PARAM_VALUE}
 * path.{NUMBER_OF_PATH} = {NAME_OF_PATH}
 * method = $_SERVER['REQUEST_METHOD'] (may be GET, POST, PUT, DELETE, OPTIONS, HEAD etc)
 * body = {BODY_STRING}
 * body.{PATH}.{TO}.{KEY} = {VALUE} (for content-types application/json and application/x-www-form-urlencoded)
 * 
 */

$extends = array(
    'device' => array(
        'type' => 'phone'
    ),
);
$body = file_get_contents('php://input');
$request = new \Ddrv\TDS\Core\Request($_SERVER, $_GET, $body, $_COOKIE, $extends);

$link = <<<JSON
{
  "key": "test",
  "rules": [
    "criteria": [
      {
        "parameter": "device.type",
        "operator": "is",
        "values": ["phone", "tablet"]
      }
    ],
    "responses": [
      "response-a"
    ]
  ],
  "responses": [
    "response-b"
  ]
}
JSON;

$tds->storage()->link()->save($link);

```

## Tokens

```php
<?php

/*
 * You can set tokens for request and set it in rules and responses.
 */

$body = file_get_contents('php://input');
$request = new \Ddrv\TDS\Core\Request($_SERVER, $_GET, $body, $_COOKIE);

// multi language site
$langSite = <<<JSON
{
  "key": "lang-site",
  "status": 301,
  "headers": [
    "location: https:\/\/{{lang}}.site.com",
    "content-type: text/plain"
  ],
  "body": "redirect to https:\/\/{{lang}}.site.com"
}
JSON;

// default site
$default = <<<JSON
{
  "key": "site",
  "status": 301,
  "headers": [
    "location: https:\/\/site.com",
    "content-type: text/plain"
  ],
  "body": "redirect to https:\/\/site.com"
}
JSON;

$tds->storage()->response()->save($langSite);
$tds->storage()->response()->save($default);
// link
$link = <<<JSON
{
  "key": "test",
  "tokens": [
    {
      "name": "lang",
      "in": "geo",
      "position": "country",
    }
  ],
  "rules": [
    "criteria": [
      {
        "parameter": "token.lang",
        "operator": "is",
        "values": ["en", "ru"]
      }
    ],
    "responses": [
      "lang-site"
    ]
  ],
  "responses": [
    "site"
  ]
}
JSON;

$extends = array(
    'geo' => array(
        'country' => 'en' // You must define it
    ),
);
$body = file_get_contents('php://input');
$request = new \Ddrv\TDS\Core\Request($_SERVER, $_GET, $body, $_COOKIE, $extends);
$tds->storage()->link()->save($link);

```

## Deleting responses and links

```php
<?php

$tds->storage()->response()->delete('response-a');
$tds->storage()->link()->delete('test');
```

## Metrics

```php
<?php

$body = file_get_contents('php://input');
$request = new \Ddrv\TDS\Core\Request($_SERVER, $_GET, $body, $_COOKIE);
$tds = new Ddrv\TDS\TDS();
$click = $tds->click($request);

/*
 * You can process the request and response data
 * $click->response()->key() - key of response
 * $click->response()->body() - body of response
 * $click->response()->headers(false) - array of response headers (for example, array('location: http://site.com'))
 * $click->response()->headers(true) - assoc array of response headers (for example, array('location' => 'http://site.com'))
 * $click->response()->status() - status of response
 * (string)$request - raw data of request
 * (string)$click->response() - raw data of response
 * $click->criteria() - criteria of current rule
 * $click->link() - link key
 * $click->tokens() - associative array of tokens
 */

$click->response()->out();

```