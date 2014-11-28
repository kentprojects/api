# KentProjects API &raquo; Database

The Database folder holds the various database elements used to build the database.

Contains:

- `tables`, for the various tables & their structures.
- `alterations`, for the various alterations made to tables.
- `procedures`, for the various procedures made to interact with the database.

There are 4 main groups of tables:

| Table | Purpose |
| ---- | ---- |
| **Core** |
| *Metadata* | Unindexed fields relating to other tables |
| *Year* | Contains 'years', exists almost entirely for semantic reasons - linked to by other tables |
| **User** |
| *User* | |
| *Authentication* | Temporary tokens for authentication which are destroyed when the user has finished authenticating |
| *Token* | Permanent tokens used to identify a user in lieu of their username & password once authenticated |
| *User-Year Map* | Links a user to a year they are part of |
| **Groups** |
| *Group* | When a group of students is formed, the metadata for it is stored here |
| *Group-Student Map* | The 'group' is linked to its students via this map |
| **Projects** |
| *Project* | When a project is proposed, its metadata is stored here |
| *Project-Supervisor Map* | Links a project to the supervisors responsible for it |