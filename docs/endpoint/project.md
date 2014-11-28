---
layout: page
title: Project
---

```http
GET /project/:id HTTP/1.1
```

This endpoint allows you to interact with a project, or create a new project.

## Get a project

```http
GET /project/:id HTTP/1.1
```

That's a simple GET request to get specific project by it's ID. That will return:

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

## Creating a project

This requires User Authentication.

```http
POST /project HTTP/1.1
```

```json
{
	"year": 2014,
	"name": "The Incredible Clever Project"
}
```

A simple POST request will create a new project and will return:

```http
HTTP/1.1 201 Created
```

```json
{
	"id": 2,
	"year": 2014,
	"name": "The Incredible Clever Project",
	"slug": "the-incredible-clever-project"
}
```

## Updating a project

```http
PUT /project/:id HTTP/1.1
```

```json
{
	"name": "Project of Supreme Cleverness"
}
```

A simple PUT request to update the existing project. This will return:

```http
HTTP/1.1 200 OK
```

```json
{
	"id": 2,
	"year": 2014,
	"name": "Project of Supreme Cleverness",
	"slug": "project-of-supreme-cleverness"
}
```

## Deleting a project

```http
DELETE /project/:id HTTP/1.1
```

A simple DELETE request to delete the existing project. This will return:

```http
HTTP/1.1 204 No Content
```