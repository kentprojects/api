# KentProjects > Intents > Join A Group

This represents an intent to join a group. [See here for more information on intents](./generic.md).

## Creating

Creating an intent to join a group involves sending a `POST` request with the handler set to `join_a_group` and 
providing a valid `group_id`:

```http
POST /intent HTTP/1.1
```
```json
{
    "handler": "join_a_group",
    "data": {
        "group_id": 1,
    }
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
    "handler": "join_a_group",
    "data": {
        "group_id": 1
    },
    "state": "open",
    "group": {
        "group_id": 1,
        "name": "The Master Commanders",
        "...": "..."
    }
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
    "handler": "join_a_group",
    "data": {
        "group_id": 1
    },
    "state": "open",
    "group": {
        "group_id": 1,
        "name": "The Master Commanders",
        "...": "..."
    }
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

The `data` object will be merged with the existing data (so, set items to `null` to remove them!) and the `state` field
will update the intent's state.

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
    "handler": "join_a_group",
    "data": {
        "group_id": 1
    },
    "state": "accepted",
    "group": {
        "group_id": 1,
        "name": "The Master Commanders",
        "...": "..."
    }
}
```