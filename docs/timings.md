# KentProjects > Timings

It's important to make sure that stuff doesn't take a ridiculous amount of time.

To look at the timings, analyse the headers of a response:

```http
GET / HTTP/1.1
```
```http
HTTP/1.1 200 OK
Content-Length: 34
Content-Type: application/json
X-Timing: 16.3ms
```
```json
"Welcome to the KentProjects API!"
```

The `X-Timing` header shows how long the request took. Too look at the timings broken down into subsections, add
`?timing` to your URL:

```http
GET /?timing HTTP/1.1
```
```http
HTTP/1.1 200 OK
Content-Length: 34
Content-Type: application/json
X-Timing-Body: true
```
```json
{
    "offset": "0.0ms",
    "length": "14.7ms",
    "children": {
        "request": {
            "offset": "1.0ms",
            "length": "13.8ms",
            "children": {
                "controller": "0.0ms +14.7ms"
            }
        }
    }
}
```

This shows a simple breakdown looking at specific subsections. If one of these takes forever, start panicking.