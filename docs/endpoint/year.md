---
layout: page
title: Year
---

## Get a list of years

```http
GET /years HTTP/1.1
```

That's a simple GET request to get all the years that have been established. That will return:

```http
HTTP/1.1 200 OK
```

```json
[
	{
		"year": 2014
	}
]
```

## Get a year

```http
GET /year/:id HTTP/1.1
```

That's a simple GET request to get specific year by it's ID. That will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"year": 2014
}
```

## Creating a year

This requires User Authentication.

```http
POST /year HTTP/1.1
```

```json
{
	"year": 2014
}
```

A simple POST request will create a new year and will return:

```http
HTTP/1.1 201 Created
```

```json
{
	"year": 2014
}
```

## Updating a year

You don't update a year. A year will update you.

## Deleting a year

In what universe can you delete a year?