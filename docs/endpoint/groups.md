---
layout: page
title: Groups
---

```http
GET /groups HTTP/1.1
```

That's a simple GET request to get all the projects. That will return:

```http
HTTP/1.1 200 OK
```

```json
[
	{
    	"id": 1,
    	"year": 2014,
    	"name": "The guys at the back of the lecture",
    	"slug": "the-guys-at-the-back-of-the-lecture"
    },
    {
    	"id": 2,
    	"year": 2014,
    	"name": "The Incredible Clever Group",
    	"slug": "the-incredible-clever-group"
    }
]
```

## Filtering groups

By adding various query string parameters, you can narrow down the list of groups to a more relevant list.

| Parameter | Type | Example | Notes |
| ---- | ---- | ---- | ---- | ---- |
| `year` | int | `2014` | This will return all the groups under a certain year. |