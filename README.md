Flowly Content API Client
=========================

# Installation

```
composer require simmcomm/content-api-client
```

# Usage

```php

require __DIR__ . '/vendor/autoload.php';

use Flowly\Content\ContentApiClient;

// $access and $secret are from Flowly app API keys module
$client = new ContentApiClient($access, $secret);

// it is possible to inject instance of
// Symfony\Contracts\HttpClient\HttpClientInterface as third parameter for
// custom initialization

// all request arguments and responses are mapped to their class
// mapping classes are located in namespaces Flowly\Content\Request and Flowly\Content\Response
// all methods in api client are accordingly typed (arguments and return value)

// public client api

// used on scene list view
$client->getScenes();

// single scene object 
$client->getScene();

// similar videos, based on scene id passed to this method
$client->getScenesSuggest();

// actors and categories lists
$client->getCategories();
$client->getActors();

// scene or actor can be star rated (1-5 scores)
$client->submitRating();

// used for portal landing page
$client->getScenesLanding();

```

# Flowly content API

- route endpoint = `'https://api-content.flowly.com'`

## GET `/scenes`

### query

- `categories: int[] = []`
    - category id filter, empty (default) means no filter
- `categoriesExclude: int[] = []`
    - exclude categories filter
- `actors: int[] = []`
    - actor id filter, empty (default) means no filter
- `orderBy: "added"|"duration"|"name"|"usage"|"rating" = "added"`
    - value is string enum, wrong value is ignored and default is used instead
        - `"usage"`: usage (clicks) counter
        - `"rating"`: user rating (likes/dislikes, stars; tbd)
- `orderDir: "asc"|"desc" = null`
    - force different sorting order
    - defaults:
        - added: desc
        - duration: desc
        - name: asc
        - usage: desc
        - rating: desc
- `offset: int = 0`
- `limit: int = 25`
- `links: bool = false`
    - if true, only links are provided instead of objects
- `videoResolution: 360|480|720|1080 = null`
    - video links will be returned for specified resolution
    - value meaning is video **height**
    - null means the highest possible resolution
- `imageResolution: 360|480|640|720|1080 = null`
    - image links will be returned for specified resolution
    - value meaning is image **width**
    - null means the highest possible resolution
    - cover image is not affected, it is always 1024 pixels wide
- `licensor: int = null`
    - only `afsc` in intial version (numeric identifier)
    - `null` means any
    - allowed values will be extended in future (as new licensors are added)
- `rating: string = ">=1.0 <=10.0"`
    - content rating filters (see content_rating.md)
    - uses semver-like strings for filtering

### responses

#### Success (HTTP 200)

```json5
{
    // always `null` for this response type
    "error": null,
    // total number of results matching the query (pagination)
    "count": 6009,
    // count of rows in current response can be derived from scenes array
    "scenes": [
        {
            // internal id, uuid
            "id": "106b55e9-2fcb-482f-b8ca-00c16d21ab6f",
            // 1: straight, 2: gay, 3: ???
            "orientation": 1,
            // downloader friendly name, max 5 tokens from description
            "name": "gemma_massey_and_sammy_jayne.mp4",
            // is description needed???
            "description": "Gemma Massey And Sammy Jayne In Big Boob Lesbian Sex Session",
            // date added, ISO8601 or RFC3339 format
            "added": "2021-01-12T13:26:22+0000",
            // duration in seconds
            "duration": 1435,
            // video hits
            "hits": 175,
            // user rating (likes-dislikes or 1..5 stars)
            "rating": 3.4,
            // see content_rating.md
            "contentRating": 9.0,
            "cover": ".../cover.jpg",
            "thumbnails": [
                {
                    "url": ".../thumbnail.jpg",
                    "contentRating": "CS9.0"
                },
                // ...
            ],
            "categories": [
                {
                    "id": 46,
                    "name": "Lesbians",
                },
                {
                    "id": 10,
                    "name": "Blonde"
                },
                // ...
            ],
            "actors": [
                {
                    "id": 43,
                    "name": "SAMMY JAYNE",
                },
                {
                    "id": 64,
                    "name": "GEMMA MASSEY"
                },
                // ...
            ],
            "videos": {
                "preview": [
                    {
                        "url": ".../1080p/preview.mp4",
                        "contentRating": "CS9.0"
                    }
                    // multiple results are possible with different 
                    // contentRating
                ],
                "teaser": [
                    {
                        "url": ".../1080p/teaser.mp4",
                        "contentRating": "CS9.0"
                    }
                ],
                "full": [
                    {
                        "url": ".../1080p/full.mp4",
                        "contentRating": "CS9.0"
                    }
                ],
            },
        },
        {
            // ...
        }
    ]
}
```

If `links==true`

```json5
{
    "error": null,
    "count": 6009,
    "scenes": [
        "https://api.content-dn.com/scene/106b55e9-2fcb-482f-b8ca-00c16d21ab6f",
        // ...
    ]
}
```

#### Error (HTTP 4xx/5xx)

```json5
{
    "error": "Whoops...",
    "scenes": [
        // empty array
    ]
}
```

## GET `/scene/{id}`

### parameters

- `id: string`: content id (uuid)

### responses

#### Success (HTTP 200)

```json5
{
    error: null,
    scene: {
        // single object as described in GET `/scenes`
    }
}
```

#### Error (HTTP 4xx/5xx)

```json5
{
    "error": "Whoops...",
    "scene": null
}
```

## GET `/scene/{id}/suggest`

Find similar content (filtered by categories, actors...)

### parameters

- `id: string`: content id (uuid)

### query

Some parameters are same as in `/scenes`, thus same rules are applied.

- `orderBy: "added"|"duration"|"name"|"usage"|"rating" = "added"`
- `orderDir: "asc"|"desc" = null`
- `links: bool = false`
- `minCount: int = 0`
    - force result count (add random results to satisfy requested count)
    - if less than 1, it will return up to `limit` count of rows
- `limit: int = 25`
    - default value is subject to change

### responses

As described in `/scenes`.

## GET `/categories`

### responses

#### Success (HTTP 200)

```json5
{
    "error": null,
    "categories": [
        {
            "id": 123,
            "name": "Blonde"
        }
        // ...
    ]
}
```

#### Error (HTTP 4xx/5xx)

```json5
{
    "error": "Whoops...",
    "categories": []
}
```

## GET `/actors`

### responses

#### Success (HTTP 200)

```json5
{
    "error": null,
    "actors": [
        {
            "id": 123,
            "name": "GEMMA MASSEY"
        }
        // ...
    ]
}
```

#### Error (HTTP 4xx/5xx)

```json5
{
    "error": "Whoops...",
    "actors": []
}
```

## POST `/rating/{type}/{id}`

### parameters

- `type: "scene"|"actor"`
- `id: string`: content id (uuid)

### cookies

- `flid: string`
    - required
    - prevent spamming and score manipulation

### body (text/plain)

An integer representing the score (within implementation bounds, e.g. 1-5):

```text
4
```

### responses

#### Success (HTTP 200)

```json5
{
    "error": null
}
```

#### Error (HTTP 4xx/5xx)

```json5
{
    "error": "Something went wrong"
}
```

##### Error types (4xx):

Code  | Description
  --- | ---
`400` | submitted value out of bounds
`401` | missing `flid` cookie
`403` | already submitted
`404` | `id` not found

## GET `/scenes/landing`

Landing page

### query

- `orderBy: "added"|"duration"|"name"|"usage"|"rating" = "added"`
- `orderDir: "asc"|"desc" = null`
- `links: bool = false`
- `videoResolution: 360|480|720|1080|null = null`
- `imageResolution: 360|480|640|720|1080|null = null`
- `licensor: "afsc" = null`
- `blockSize: int = 25`
    - results per block

### responses

#### Success (HTTP 200)

```json5
{
    // always `null` for this response type
    "error": null,
    "blocks": [
        {
            "description": "Most viewed",
            "scenes": [
                // scene objects
            ]
        },
        {
            "description": "Recently featured",
            "scenes": [
                // scene objects
            ]
        },
        // ...
    ]
}
```

If `links==true`

```json5
{
    // always `null` for this response type
    "error": null,
    "blocks": [
        {
            "description": "Most viewed",
            "scenes": [
                "https://api.content-dn.com/scene/106b55e9-2fcb-482f-b8ca-00c16d21ab6f",
                // ...
            ]
        },
        // ...
    ]
}
```

#### Error (HTTP 4xx/5xx)

```json5
{
    "error": "Whoops...",
    "blocks": [
        // always empty array for this response type
    ]
}
```
