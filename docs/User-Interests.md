# KentProjects > User > Interests

User interests are listed as part of the User object:

```json
{
    "id": 4,
    "...": "...",
    "interests": [
        "PHP", "Node.JS", "MySQL", "GTA V"
    ],
    "...": "..."
}
```

To update them, pass them to a `PUT /staff/:id` or `PUT /student/:id` request:

```http
PUT /student/4 HTTP/1.1
```
```json
{
    "interests": [
        "PHP", "Node.JS", "MySQL"
    ]
}
```

This list of strings will overwrite the existing list of strings, so don't send changes, send the complete list.