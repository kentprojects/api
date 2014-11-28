---
layout: page
title: Requests
---

The API communicates over a simple HTTP REST interface, taking query strings and JSON-encoded POST data as requests and 
returning JSON-encoded responses.

In order to communicate with the API, you will need to be able to:

- Make HTTP requests (via CURL or some other means)
- JSON encode & decode data
- Hash strings using the `md5` algorithm

You will also need an `application_key` and `application_secret` from us. These will be generated on a case-by-case
basis, and could be revoked at any time. We will request contact details from you so we can get in touch with you
regarding your account, to hand out new key / secret pairs when required and to discuss any irregularities we detect
with requests using your key.

**At no point should you ever reveal your `application_secret` to anybody. Please ensure it is kept server-side and is
never revealed.**

## Signing Requests

All requests to the API must be signed using your `application_secret`, so we can validate that the request originated
from you.

Signing requests involves including your `application_key` and an expiry time in the query string, as well as generating
and including a `signature` with the query string.

To create a signature:

- Take the contents of the `GET` query string and put them into an array.
- Remove any `GET` keys named `signature`.
- Sort the array alphabetically in ascending order of keys.
- Ensure all the keys and values of the array are all strings (and not integers, or some other type).
- JSON-encode the array, and prepend a salt and your `application_secret` to the JSON string.
- Finally, `md5` the result to create the request `signature`.

In an english list, that sounds very complicated, so take a look at this PHP sample, which hopefully will make it easier
to understand:

```php
<?php
/**
 * Take the contents of the `GET` query string and put them into an array.
 */
$_GET = array(
	"key" => "SomeImportantApplicationKeyWeGaveYou",
	"expires" => time() + 300
);
/**
 * Remove any `GET` keys named `signature`.
 */
unset($_GET["signature"]);
/**
 * Sort the array alphabetically in ascending order of keys.
 */
ksort($_GET);
/**
 * Ensure all the keys and values of the array are all strings (and not integers, or some other type).
 */
array_walk(
	$_GET,
	function (&$v)
	{
		$v = (string)$v;
	}
);
/**
 * JSON-encode the array, and prepend a salt and your `application_secret` to the JSON string.
 * Finally, `md5` the result to create the request `signature`.
 */
$_GET["signature"] = md5(
	"SomeImportantSaltWeGaveYou" . "SomeImportantApplicationSecretWeGaveYou" . json_encode($_GET)
);
echo http_build_query($_GET, null, "&");
/**
 * expires=1417136734&
 * key=SomeImportantApplicationKeyWeGaveYou&
 * signature=5f2e8f39e5870e68f752b01ed3beb941
 */
```

For `GET` requests with other query string parameters, as long as those parameters don't include `expires`, `key` or
`signature`, you'll be fine. And we will ensure that no query string parameters conflict.

For `POST` and `PUT` requests (and, in some rare cases, `DELETE` requests too) that contain POST data to be sent, the
POST data should be sent as a JSON-encoded body with a relevant `Content-Type` (`application/json`) and `Content-Length`
headers with it.

**Most (if not all) requests need to be signed, no matter what HTTP Method you use.**

This means even `POST`, `PUT` & `DELETE` requests need a `signature` and the other parameters in their `GET` query
string, even though they're not `GET` requests.