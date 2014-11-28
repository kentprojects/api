---
layout: page
title: Group
---

```http
GET /group HTTP/1.1
```

This endpoint allows you to interact with a group, or create a new group.

## Get a group

```http
GET /group/:id HTTP/1.1
```

That's a simple GET request to get specific group by it's ID. That will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"id": 22,
	"year": 2014,
	"name": "The guys at the back of the lecture",
	"slug": "the-guys-at-the-back-of-the-lecture"
}
```

## Creating a group

This requires User Authentication.

```http
POST /group HTTP/1.1
```

```json
{
	"year": 2014,
	"name": "The Incredible Clever Group"
}
```

A simple POST request will create a new group and will return:

```http
HTTP/1.1 201 Created
```

```json
{
	"id": 2,
	"year": 2014,
	"name": "The Incredible Clever Group",
	"slug": "the-incredible-clever-group"
}
```

## Updating a group

```http
PUT /group/:id HTTP/1.1
```

```json
{
	"name": "Knights of Supreme Cleverness"
}
```

A simple PUT request to update the existing group. This will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"id": 2,
	"year": 2014,
	"name": "Knights of Supreme Cleverness",
	"slug": "knights-of-supreme-cleverness"
}
```

## Deleting a group

```http
DELETE /group/:id HTTP/1.1
```

A simple DELETE request to delete the existing group. This will return:

```http
HTTP/1.1 204 No Content
```