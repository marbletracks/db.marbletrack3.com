# Marble Track 3 — Read-Only API Design Proposal

## Purpose

Allow AI agents (and potentially other tools) to query the site
the same way a human browses it — drilling into Parts, Tracks,
Workers, Moments, and SSOP data. Primary use case: continuity
checking and helping Rob fill in data gaps.

## Authentication

API key in header, matching the pattern used on mg.robnugen.com.

```
X-API-Key: <key>
```

Keys tracked in a `api_keys` table with rate limiting and usage stats.
Rob gets unlimited free access. Other agents pay per request.

## Base URL

```
https://db.marbletrack3.com/api/v1/
```

## Endpoints

### Parts

#### GET /api/v1/parts
List all parts with summary info.

Query params:
- `track_id` — filter to parts in a specific track
- `unassigned=1` — only parts not assigned to any track
- `needs_description=1` — only parts missing descriptions
- `search=term` — search by name or alias
- `limit`, `offset` — pagination (default 50)

Response:
```json
{
  "parts": [
    {
      "part_id": 42,
      "part_alias": "5poss",
      "part_name": "Fifth Placed Outer Spiral Support",
      "part_description": "Bottom of Caret Splitter Right Track",
      "has_description": true,
      "tracks": [
        {
          "track_id": 6,
          "track_name": "Outer Spiral",
          "part_role": "support",
          "is_exclusive": true
        }
      ],
      "moment_count": 3,
      "photo_count": 1
    }
  ],
  "total": 147
}
```

#### GET /api/v1/parts/{id}
Full detail for one part.

Response includes everything from the list plus:
- `moments` — all moments referencing this part, chronologically
- `photos` — all photo URLs
- `oss_status` — SSOP data if applicable (ssop_mm, heights)
- `shortcode` — the shortcode format for this part

#### GET /api/v1/parts/{id}/moments
All moments for a part, ordered by moment_date or frame number.

Response:
```json
{
  "part_id": 42,
  "part_name": "Fifth Placed Outer Spiral Support",
  "moments": [
    {
      "moment_id": 101,
      "moment_date": "2020-03-15",
      "frame_start": 567,
      "frame_end": 675,
      "take_id": 14,
      "take_name": "Snippet 8",
      "notes": "[worker:gc] cuts [part:5poss] to height",
      "notes_expanded": "G Choppy cuts Fifth Placed Outer Spiral Support to height",
      "translations": [
        {
          "perspective_type": "worker",
          "perspective_name": "G Choppy",
          "text": "Cut the fifth support to 31.5mm"
        }
      ]
    }
  ]
}
```

### Tracks

#### GET /api/v1/tracks
List all tracks.

Query params:
- `entity_type=marble|worker|mixed`
- `type=transport|splitter|landing_zone`

Response:
```json
{
  "tracks": [
    {
      "track_id": 6,
      "track_alias": "os",
      "track_name": "Outer Spiral",
      "entity_type": "marble",
      "marble_sizes": ["medium", "large"],
      "is_transport": true,
      "is_splitter": false,
      "is_landing_zone": false,
      "part_count": 23,
      "upstream_tracks": [{"track_id": 1, "track_name": "Triple Splitter System"}],
      "downstream_tracks": [{"track_id": 3, "track_name": "The First Track"}]
    }
  ]
}
```

#### GET /api/v1/tracks/{id}
Full detail including all component parts with roles.

#### GET /api/v1/tracks/{id}/parts
Parts in this track, ordered by role priority (main > connector > guide > support).

### Workers

#### GET /api/v1/workers
List all workers with abilities summary.

#### GET /api/v1/workers/{id}
Full detail including description, abilities, ghost status.

#### GET /api/v1/workers/{id}/moments
All moments involving this worker, chronologically.

### Moments

#### GET /api/v1/moments
List moments with filters.

Query params:
- `part_id` — moments involving this part
- `worker_id` — moments involving this worker
- `take_id` — moments in this take
- `date_from`, `date_to` — date range
- `frame_from`, `frame_to` — frame range (within a take)

#### GET /api/v1/moments/{id}
Full detail with all translations/perspectives.

### SSOP (Outer Spiral Support Status)

#### GET /api/v1/oss/status
All SSOP entries ordered by position (ssop_mm ASC).

Response:
```json
{
  "supports": [
    {
      "part_alias": "0poss",
      "part_name": "Zeroth Placed Outer Spiral Support",
      "ssop_label": "SSOP000",
      "ssop_mm": 0.0,
      "height_orig": 22.0,
      "height_best": 25.21,
      "height_now": 22.0,
      "height_delta": -3.21,
      "placement_order": 0,
      "last_updated": "2024-01-15T10:30:00Z"
    }
  ],
  "summary": {
    "total_supports": 23,
    "max_ssop_mm": 830,
    "height_range": {"min": 22.0, "max": 41.0},
    "supports_needing_adjustment": 12
  }
}
```

### Takes / Snippets

#### GET /api/v1/takes
List all takes, with snippet flag.

#### GET /api/v1/takes/{id}/moments
Moments in this take, ordered by frame_start.

### Notebooks (lower priority)

#### GET /api/v1/notebooks
#### GET /api/v1/notebooks/{id}/pages
#### GET /api/v1/notebooks/{id}/pages/{page}/columns
#### GET /api/v1/notebooks/{id}/pages/{page}/columns/{col}/tokens

### Continuity Check (special endpoint)

#### GET /api/v1/continuity/parts/{id}
Returns a timeline of everything known about a part, with flags for potential issues.

```json
{
  "part_id": 42,
  "part_name": "Fifth Placed Outer Spiral Support",
  "timeline": [
    {"event": "created/cut", "date": "2020-01-10", "frame": 450, "take": 12},
    {"event": "carried_by", "worker": "G Choppy", "date": "2020-01-10", "frame": 460, "take": 12},
    {"event": "placed", "date": "2020-01-12", "frame": 500, "take": 12},
    {"event": "glued", "date": null, "frame": null, "take": null}
  ],
  "flags": [
    {"type": "missing_data", "message": "No glue date recorded"},
    {"type": "gap", "message": "30-day gap between carried and placed"}
  ],
  "current_state": "placed_not_glued"
}
```

## Implementation Notes

- All responses JSON with `Content-Type: application/json`
- Shortcodes in `notes` fields returned both raw and expanded
- Dates in ISO 8601 format
- Error responses: `{"error": "message", "code": 404}`
- Rate limit headers: `X-RateLimit-Remaining`, `X-RateLimit-Reset`
- Built on existing Repository classes — no new DB abstraction needed
- Router: new `wwwroot/api/v1/index.php` with simple path matching
