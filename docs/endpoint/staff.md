---
layout: page
title: Staff
---

## Get a list of staff

```http
GET /projects HTTP/1.1
```

That's a simple GET request to get all the staff. That will return:

```http
HTTP/1.1 200 OK
```

```json
[
	{
		"id": 22,
		"email": "someperson@kent.ac.uk"
	}
]
```

## Get a member of staff

```http
GET /staff/:id HTTP/1.1
```

That's a simple GET request to get specific staff member by their ID. That will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"id": 22,
	"email": "someperson@kent.ac.uk"
}
```

## Creating a staff member

Staff members aren't created. They login and exist.

## Updating a staff member

```http
PUT /staff/:id HTTP/1.1
```

```json
{
	"first_name": "John",
	"last_name": "Crawford"
}
```

A simple PUT request to update the existing staff member. This will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"id": 22,
    "email": "someperson@kent.ac.uk",
    "first_name": "John",
    "last_name": "Crawford"
}
```

## Deleting a staff member

```http
DELETE /staff/:id HTTP/1.1
```

A simple DELETE request to delete an existing staff member. This will return:

```http
HTTP/1.1 204 No Content
```