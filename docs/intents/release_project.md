# KentProjects > Intents > Release a project

This represents an intent to release a project. [See here for more information on intents](./generic.md).

## Creating

Creating an intent to join a group involves sending a `POST` request with the handler set to `release_project` and 
providing a valid `group_id`:

```http
POST /intent HTTP/1.1
```
```json
{
    "handler": "release_project",
    "data": {
        "project_id": 1,
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
    "handler": "release_project",
    "data": {
        "project_id": 1
    },
    "state": "accepted"
}
```