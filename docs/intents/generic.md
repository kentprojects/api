# KentProjects > Intents > Generic

A generic intent to do something.

An intent is made up of:

| Field | Description |
| ----- | ----------- |
| id | A unique Intent ID |
| user | The user undertaking the intent. |
| handler | The name of the intent being undertaken. |
| data | A simple key-value store for the intent. |
| state | The state of the intent (`open`, `accepted`, `rejected`) |

## Creating

First off, to create one:

```http
POST /intent HTTP/1.1
```
```json
{
    "handler": "generic",
    "data": {
        "anything": "Some Value"
    }
}
```

And you'll get back:

```http
HTTP/1.1 201 Created
```
```json
{
    "id": 1,
    "user": {
        "id": 3,
        "email": "mh472@kent.ac.uk",
        "...": "..."
    },
    "handler": "generic",
    "data": {
        "anything": "Some Value"
    },
    "state": "open
}
```

## Getting

Once you have an `id`, you can make `GET` requests to fetch data.

```http
GET /intent/:id HTTP/1.1
```

Which will return an intent object (exactly like above):

```http
HTTP/1.1 200 OK
```
```json
{
    "id": 1,
    "user": {
        "id": 3,
        "email": "mh472@kent.ac.uk",
        "...": "..."
    },
    "handler": "generic",
    "data": {
        "anything": "Some Value"
    },
    "state": "open"
}
```

## Updating

To update the intent, you can make a `PUT` request like so:

```http
PUT /intent/:id HTTP/1.1
```
```json
{
    "data": {
        "anything": null,
        "this": "will be merged",
        "with": "the existing data object"
    },
    "state": "accepted"
}
```

The `data` object will be merged with the existing data (so, set items to `null` to remove them!) and the `state` field
will update the intent's state.

Which will return the updated object:

```http
HTTP/1.1 200 OK
```
```json
{
    "id": 1,
    "user": {
        "id": 3,
        "email": "mh472@kent.ac.uk",
        "...": "..."
    },
    "handler": "generic",
    "data": {
        "this": "will be merged",
        "with": "the existing data object"
    },
    "state": "accepted"
}
```