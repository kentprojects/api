---
layout: page
title: Students
---

```http
GET /students HTTP/1.1
```

That's a simple GET request to get all the students. That will return:

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

## Filtering students

By adding various query string parameters, you can narrow down the list of projects to a more relevant list.

| Parameter | Type | Example | Notes |
| ---- | ---- | ---- | ---- | ---- |
| `year` | int | `2014` | This will return all the students under a certain year. |