# KentProjects > Groups

A group represents a collection of students (looking to) undertak(e)ing a project.

## Get an individual group

```http
GET /group/:id HTTP/1.1
```

This request will get an individual group object. Including such information as the students in the group, the project
the group is undertaking, and so on.

```json
{
    "id": 1,
    "year": "2014",
    "name": "The Master Commanders",
    "project": {
        "id": 1,
        "year": "2014",
        "group": 1,
        "name": "Student Project Support System",
        "slug": "student-project-support-system",
        "description": null,
        "creator": {
            "id": 3,
            "email": "mh471@kent.ac.uk",
            "name": "Matt House",
            "first_name": "Matt",
            "last_name": "House",
            "role": "student",
            "bio": null,
            "created": "2014-11-21 21:31:52",
            "lastlogin": "2014-01-01 00:00:00",
            "updated": "2014-12-16 16:47:06"
        },
        "created": "2015-02-16 09:42:06",
        "updated": "2015-02-16 09:42:06"
    },
    "students": [
        {
            "id": 3,
            "email": "mh471@kent.ac.uk",
            "name": "Matt House",
            "first_name": "Matt",
            "last_name": "House",
            "role": "student",
            "bio": null,
            "created": "2014-11-21 21:31:52",
            "lastlogin": "2014-01-01 00:00:00",
            "updated": "2014-12-16 16:47:06"
        },
        {
            "id": 4,
            "email": "jsd24@kent.ac.uk",
            "name": "James Dryden",
            "first_name": "James",
            "last_name": "Dryden",
            "role": "student",
            "bio": null,
            "created": "2014-11-27 19:19:35",
            "lastlogin": "2014-01-01 00:00:00",
            "updated": "2014-12-16 16:47:09"
        },
        {
            "id": 5,
            "email": "mjw59@kent.ac.uk",
            "name": "Matthew Weeks",
            "first_name": "Matthew",
            "last_name": "Weeks",
            "role": "student",
            "bio": null,
            "created": "2014-11-27 20:12:15",
            "lastlogin": "2014-01-01 00:00:00",
            "updated": "2014-12-16 16:47:09"
        }
    ],
    "creator": {
        "id": 3,
        "email": "mh471@kent.ac.uk",
        "name": "Matt House",
        "first_name": "Matt",
        "last_name": "House",
        "role": "student",
        "bio": null,
        "created": "2014-11-21 21:31:52",
        "lastlogin": "2014-01-01 00:00:00",
        "updated": "2014-12-16 16:47:06"
    },
    "created": "2015-02-16 09:42:06",
    "updated": "2015-02-16 09:42:06"
}
```