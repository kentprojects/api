# KentProjects &raquo; Notifications

Notifications are generated when a user makes certain requests to the API. The API will complete the request in question
and queue a notification to be sent to the user.

Notifications are queued in a UNIX pipe, and are generated one-by-one and inserted into the database:

```
{Notification::queue($type, $actor, $references, $targets);}  
    |  
    v  
  Queue a JSON-encoded instruction in /var/www/notifications-pipe  
      |  
      v  
    Execute /notifications.php with the JSON-encoded instruction as an argument.
```

Checks are done in `Notification::queue` and in the main `/notifications.php` script, to minimise the risk of errors
whilst a notification is being generated.

Notifications are built around a `type`, which is any one of the keys at `Model_Notification::$typeStrings`. The type
relates to a list of strings that define the content of the notification. The context of the notification can differ
depending on the role of the user, for example inside a group the students should see "*your group*" rather than the
group name, so this structure aims to accommodate for that.

Each string refers to a different context:

- `default` refers to what anybody could see, with no context.
- `group_member` refers to the context of a group member.
- `project_supervisor` refers to the context of a supervisor.

----

## To add new types

All you need to do is add a new array of strings in `Model_Notification::$typeStrings`, and the structure will adapt and
you'll be able to start generating new notification types straight away!

----

Being designed this way, the notifications will take up very little space in the database (given the amount of them that
there will be, that statement couldn't be more `false`) per notification, and per recipient, and with the caching of
attributes & the strings we stand a fighting chance against the mass number of users expected to use this platform!