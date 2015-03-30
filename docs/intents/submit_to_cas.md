# KentProjects > Intents > Submit To CAS

This represents an intent to submit a request to CAS. [See here for more information on intents](./generic.md).

## Creating

Creating an intent to join a group involves sending a `POST` request with the handler set to `submit_to_cas`:

```http
POST /intent HTTP/1.1
```
```json
{
    "handler": "submit_to_cas"
}
```

Optionally you can provide `additional: true` in the `data` if you wish to alert the CAS office that this particular
group requires additional information and will be coming to get an extended details sheet.

```http
POST /intent HTTP/1.1
```
```json
{
    "handler": "submit_to_cas",
    "data": {
        "additional": true
    }
}
```

And you'll get back an intent object:

```http
HTTP/1.1 201 Created
```
```json
{
    "id": 2,
    "user": {
        "id": 4,
        "email": "jsd24@kent.ac.uk",
        "...": "..."
    },
    "handler": "submit_to_cas",
    "data": {},
    "state": "accepted"
}
```