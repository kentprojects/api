# KentProjects > Intents > Access Year

This represents an intent to access a year. [See here for more information on intents](./generic.md).

## Creating

Creating an intent to access a year involves sending a `POST` request with the handler set to `access_year`:

```http
POST /intent HTTP/1.1
```
```json
{
    "handler": "access_year"
}
```

And you'll get back an intent object with a group object:

```http
HTTP/1.1 201 Created
```
```json
{
    "id": 1,
    "user": {
        "id": 4,
        "email": "jsd24@kent.ac.uk",
        "...": "..."
    },
    "handler": "access_year",
    "data": {},
    "state": "open"
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
        "id": 4,
        "email": "jsd24@kent.ac.uk",
        "...": "..."
    },
    "handler": "access_year",
    "data": {},
    "state": "open"
}
```

## Updating

To update the intent, you can make a `PUT` updating the state of the intent.

```http
PUT /intent/:id HTTP/1.1
```
```json
{
    "state": "accepted"
}
```

And you will receive:

```http
HTTP/1.1 200 OK
```
```json
{
    "id": 1,
    "user": {
        "id": 4,
        "email": "jsd24@kent.ac.uk",
        "...": "..."
    },
    "handler": "access_year",
    "data": {},
    "state": "accepted"
}
```