---
layout: page
title: Projects
---

```http
GET /projects HTTP/1.1
```

This endpoint allows you to get a list of projects based on certain criteria.

## Get all the projects

```http
GET /projects HTTP/1.1
```

That's a simple GET request to get all the projects. That will return:

```http
HTTP/1.1 200 OK
```

```json
[
	{
		"id": 22
	}
]
```

## Filtering projects

By adding various query string parameters, you can narrow down the list of projects to a more relevant list.

| Parameter | Type | Example | Notes |
| ---- | ---- | ---- | ---- | ---- |
| `fields` | CSV of strings | `id,name,slug` | This will return all the project data with certain project IDs |
| `ids` | CSV of ints| `1,2,3,4` | This will return all the project data with certain project IDs |
| `year` | int | `2014` | This will return all the projects under a certain year. |