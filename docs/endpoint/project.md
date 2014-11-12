---
layout: document
---

# Project

A project represents a final year project that a set of students (a "[group][group]") will work on.

---

## Getting a Project

A simple request to:

`GET /project/:id`

Where `:id` is the project ID.

### Example Request

{% highlight http %}
GET /project/22 HTTP/1.1
Host: api.kentprojects.com
{% endhighlight %}

### Example Response

{% highlight http %}
HTTP/1.1 200 OK
{% endhighlight %}

{% highlight json %}
{
	"id": 22
}
{% endhighlight %}

---

## Creating a new Project

---

[group]: /endpoint/group.html