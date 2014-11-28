---
layout: page
title: Student
---

```http
GET /student HTTP/1.1
```

This endpoint allows you to interact with a student.

## Get a student

```http
GET /student/:id HTTP/1.1
```

That's a simple GET request to get specific student by their ID. That will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"id": 22,
	"email": "someperson@kent.ac.uk"
}
```

## Creating a student

Students aren't created. They login and exist.

## Updating a student

```http
PUT /student/:id HTTP/1.1
```

```json
{
	"first_name": "Matt",
	"last_name": "House"
}
```

A simple PUT request to update the existing student. This will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"id": 22,
	"email": "someperson@kent.ac.uk",
	"first_name": "Matt",
	"last_name": "House"
}
```

## Deleting a staff member

```http
DELETE /student/:id HTTP/1.1
```

A simple DELETE request to delete an existing student. This will return:

```http
HTTP/1.1 204 No Content
```