# Discussion: The Source of Truth for Moment Relationships

This document explores the architectural challenge of associating Moments with Workers and Parts, considering two types of moments:
1.  **Perspective Moments:** Moments with rich, translated descriptions from multiple points of view.
2.  **Simple Moments:** Moments with a single, generic description that don't have shortcodes or translations but still need to be linked to an entity.

The current implementation, which relies solely on `moment_translations`, fails to handle "Simple Moments."

## The Core Problem

We have two conflicting sources of truth:
*   `moment_translations`: The "rich" source, containing perspective-specific notes.
*   A simple linking mechanism (like the old `workers_2_moments`): The "simple" source, for basic associations.

How do we reconcile these without creating data integrity issues or overly complex code?

## Option 1: Re-introduce and Sync Linking Tables (The "Belt and Suspenders" Approach)

This approach treats the linking tables (`workers_2_moments`, `parts_2_moments`) as the definitive list of which moments belong to an entity, while `moment_translations` provides optional, enhanced descriptions.

**Workflow:**
1.  **On the Worker/Part Edit Page:** The UI for adding/removing moments would directly edit the `workers_2_moments` or `parts_2_moments` table. This becomes the primary way to create an association.
2.  **On the Moment Edit Page:** When a moment is saved and translations are generated from its shortcodes, the system would *also* automatically add corresponding entries to the `workers_2_moments` and `parts_2_moments` tables. This keeps the data synchronized.
3.  **Frontend Loading (`loadMoments`)**: The query would be reversed from our last change. It would `JOIN` from `workers_2_moments` to `moments`, and then `LEFT JOIN` to `moment_translations` to get the perspective note if it exists.

**Pros:**
*   **Reliable:** There is one, unambiguous place (`*_2_moments` tables) to look up all moments for a worker/part.
*   **Handles All Cases:** Works for both "Simple" and "Perspective" moments seamlessly.
*   **Preserves Sort Order:** We can once again use the `sort_order` column in the linking tables.

**Cons:**
*   **Data Duplication:** The relationship is stored in two places (`*_2_moments` and `moment_translations`), which you rightly dislike. This creates a risk of them becoming out of sync if a bug is introduced in the sync logic.
*   **Slightly More Complex Save Logic:** The Moment save process becomes responsible for updating three tables (`moments`, `moment_translations`, and `*_2_moments`).

## Option 2: A Unified "Relationships" Table (The "Single Source of Truth" Approach)

This approach replaces both the linking tables and the `moment_translations` table with a single, more comprehensive table. Let's call it `moment_relationships`.

**Proposed `moment_relationships` Schema:**
*   `moment_id` (FK)
*   `entity_id` (the ID of the worker or part)
*   `entity_type` ('worker' or 'part')
*   `translated_note` (TEXT, NULLable) - The perspective-specific note.
*   `is_significant` (BOOL)
*   `sort_order` (INT)

**Workflow:**
1.  **On the Worker/Part Edit Page:** Adding a "Simple Moment" creates a new row in `moment_relationships` with the `moment_id`, `entity_id`, `entity_type`, and a `sort_order`. The `translated_note` would be `NULL`.
2.  **On the Moment Edit Page:** When perspective notes are generated, the system performs an "UPSERT" (UPDATE or INSERT) on `moment_relationships`. It finds the matching row (`moment_id`, `entity_id`, `entity_type`) and fills in the `translated_note`.
3.  **Frontend Loading (`loadMoments`)**: The query becomes very clean. It selects from `moment_relationships`, joins to `moments`, and uses `COALESCE(mr.translated_note, m.notes)` to get the correct description.

**Pros:**
*   **No Data Duplication:** A single record defines the relationship *and* holds the optional translation. This is the cleanest data model.
*   **Handles All Cases:** Manages both simple and perspective moments in one place.
*   **All Features Included:** Naturally supports sorting and significance flags.
*   **Simplified Logic:** The loading logic is straightforward.

**Cons:**
*   **Requires Migration:** This is the biggest change. It would require migrating data from the existing `moment_translations` table and re-creating the simple associations.

## Recommendation

**Option 2 (Unified `moment_relationships` Table)** is the most robust, long-term solution. It solves the problem elegantly at the database level, which simplifies the application code and eliminates the risks of data duplication. While it requires a migration step, the long-term benefits of a clean, single source of truth are significant.

For now, we can proceed with **Option 1** as an interim step if a full migration is too disruptive. It would fix the immediate problem, and we could plan for a transition to Option 2 later. However, if we are comfortable with the migration, jumping directly to Option 2 is the superior path.
