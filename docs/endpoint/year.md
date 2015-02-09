---
layout: page
title: Year
---

## Get a list of years

```http
GET /years HTTP/1.1
```

That's a simple GET request to get all the years that a user is involved in. This will return:

```http
HTTP/1.1 200 OK
```

```json
[
	{
		"year": 2014,
		"role_convener": 1,
		"role_supervisor": 0,
		"role_secondmarker": 0
	}
]
```

Depending on their privileges, this will show a `1` next to the roles they are allowed to act as in a certain year.

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

## Statistics For A Year

```http
GET /year/:id/stats HTTP/1.1
```

Returns:

```http
HTTP/1.1 200 OK
```

```json
{
    "total_students": 3,
    "total_students_in_groups": 3,
    "total_students_in_projects": 3,
    "total_staff": 3,
    "total_supervisors": 2,
    "total_secondmarkers": 2
}
```

## Supervisors For A Year

```http
GET /year/:id/supervisors HTTP/1.1
```

This will return a list of user objects for supervisors in that year:

```http
HTTP/1.1 200 OK
```
```json
[
    {
        "id": 2,
        "email": "J.S.Crawford@kent.ac.uk",
        "name": "John Crawford",
        "first_name": "John",
        "last_name": "Crawford",
        "role": "staff",
        "created": "2014-11-21 21:31:46",
        "lastlogin": "2014-01-01 00:00:00",
        "updated": "2014-12-16 16:47:03"
    },
    {
        "id": 6,
        "email": "supervisor2@kent.ac.uk",
        "name": "Stuart Supervisor",
        "first_name": "Stuart",
        "last_name": "Supervisor",
        "role": "staff",
        "created": "2014-11-28 11:10:05",
        "lastlogin": "2014-01-01 00:00:00",
        "updated": "2014-12-16 16:47:14"
    }
]
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