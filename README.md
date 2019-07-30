# xmaze
A collaborative effort to create *structured data* about **secret, locked locations** inspired by [Inyuki's idea](http://www.halfbakery.com/idea/3D_20Study_20Maze).

**To play:**

Visit my maze at: [http://maze.mindey.com](http://maze.mindey.com).

**To start your own:**

Demo [youtube video](https://youtu.be/VCb7PpFJIj0), alternatively: [mp4 (115MB)](https://wiki.mindey.com/shared/shots/e7291ca09c793319c893a9dd1-xmaze-intro.mp4).

```
git clone https://github.com/mindey/xmaze.git xmaze
cd xmaze
php -S localhost:8000 -t maze maze/index.php
```
and visit [localhost:8000](http://localhost:8000) on your machine. Once satisfied, just sync it with some domain where PHP is available, for example, by:

**To publish:**
```
rsync -aPzr /path/to/xmaze/maze/* --rsh='ssh -p22' user@host:/path/to/public_html/
```

Note: if you get a long waiting under redirect, it might be that your pgae doesn't exist.

Note: you might need to add the following **.htaccess** file to directory:
```
Options -MultiViews
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

## Secret Locked Locations

The goal is to introduce content to learners, students, etc. by enciting them to solve problems in order to reveal secrets. I call the format for rooms and doors between them, defining a maze, a ``zone``. Below is the beginning for a schema definition for secret locked locations.

Difference between room and door, is that rooms provide rewards, and doors provide challenges.

**room**
```{json}
{
  "@context": {
    "maze": {
        "room": {
          "@id": "http://github.com/mindey/xmaze/room/tbd",
          "@type": "@id"
        },
    },
    "xsd": "http://www.w3.org/2001/XMLSchema#"
  }
}

```
**door**
```{json}
{
  "@context": {
    "maze": {
        "door": {
          "@id": "http://github.com/mindey/xmaze/door/tbd",
          "@type": "@id"
        },
    },
    "xsd": "http://www.w3.org/2001/XMLSchema#"
  }
}
```


# References
- https://www.w3.org/TR/json-ld/#advanced-context-usage
- http://www.heppnetz.de/ontologies/goodrelations/v1
