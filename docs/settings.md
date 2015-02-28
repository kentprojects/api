# KentProjects > Settings

Settings are simple key-value pairs that are accessible to an application and shared with a user.

They are directly connected to the authentication tokens.

## Get settings

Settings are included in the [`/me` request](./me.md) endpoint, or you can specifically request a new copy of the
current settings like so:

```http
GET /me/settings HTTP/1.1
```

And you'll get back:

```http
HTTP/1.1 200 OK
Content-Type: application/json
```
```json
{
    "music": [
        "Franz Ferdinand",
        "Ed Sheeran"
    ],
    "london": "calling"
}
```

## Update Settings

To update settings, a `PUT` request to `/me/settings` will allow you to update the settings.

```http
PUT /me/settings HTTP/1.1
```
```json
{
    "this is the tale": "of Captain Jack Sparrow",
    "pirate so brave": "on the seven seas"
}
```

This will return an updated object with your information:

```http
HTTP/1.1 200 OK
Content-Type: application/json
```
```json
{
    "music": [
        "Franz Ferdinand",
        "Ed Sheeran"
    ],
    "london": "calling",
    "this is the tale": "of Captain Jack Sparrow",
    "pirate so brave": "on the seven seas"
}
```

To remove a key, send a `PUT` request setting the key to `null`:

```http
PUT /me/settings HTTP/1.1
```
```json
{
    "music": null
}
```

Which will delete the key:

```http
HTTP/1.1 200 OK
Content-Type: application/json
```
```json
{
    "london": "calling",
    "this is the tale": "of Captain Jack Sparrow",
    "pirate so brave": "on the seven seas"
}
```