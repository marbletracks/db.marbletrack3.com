# Project Plan: Perspective-Based Moment History

This document outlines the chosen solution and implementation plan for improving how "Moments" are displayed in the "History" section on the frontend website.

## 1. The Goal

When viewing the history on a specific Worker or Part page, the description of each event should be tailored to that entity's perspective, avoiding redundant information and creating a more natural narrative.

**Example:** On **Reversible Guy's** page, instead of seeing `Reversible Guy get stick and place 2poss`, the user should simply see `get stick and place 2poss`. On another character's page involved in the same event, they would see a description from *their* perspective.

## 2. The Chosen Solution: Perspective-Based Translations

We will implement a system of perspective-based translations for each Moment.

*   The main `moments.notes` field will become an **internal-only** description, written concisely with shortcodes (e.g., `[worker:rg] received [part:19poss] from [worker:mrg]`).
*   A new `moment_translations` table will store the public-facing narrative text for each perspective involved in the moment.

### `moment_translations` Table Schema

*   `moment_id`: Foreign key to the `moments` table.
*   `perspective_entity_id`: The ID of the Worker or Part.
*   `perspective_entity_type`: A string, either `'worker'` or `'part'`.
*   `translated_note`: The narrative text for that specific perspective.
*   (Primary Key: `moment_id`, `perspective_entity_id`, `perspective_entity_type`)

## 3. Implementation Plan

This project will be broken down into the following steps.

### :DONE: Step 1: Fix the Moment Admin Page
The admin page for editing a Moment (`/admin/moments/moment.php`) must handle shortcodes like Parts and Workers do.

* Add JS to handle **Shortcode Expansion:** As the admin types in the main `notes` field, the JS allows easily replace `rg` with `[worker:reversible-guy]` which can be expanded into a link on the frontend.

### :DONE: Step 2: Create the `moment_translations` Table
First, we need to create the new database table to store the translations. A SQL script will be created and executed.

### Step 3: :DONE: Enhance the Moment Admin Page
The admin page for editing a Moment (`/admin/moments/moment.php`) will be updated to facilitate the new workflow.

3a: :DONE: a read-only area below it will show the fully expanded text (e.g., "Reversible Guy received 19th Placed Outer Spiral Support from Mr Greene") to provide immediate feedback. This will likely require an AJAX endpoint that processes the expanded shortcodes.     The logic for this has already been written somewhere (called by frontend generator script)

3b: :DONE: **Dynamic Perspective Fields:** Based on the shortcodes entered in the `notes` field, the page will dynamically generate a text area for each unique Worker and Part mentioned.
3c: :DONE: **Pre-populate Perspective Fields:** Initially, these new text areas will be automatically filled with the expanded shortcode text, providing a starting point for the admin to rewrite from that entity's perspective.

### Step 4: :DONE: Implement Save Logic

Step 4a: :DONE: When the admin saves the Moment form:
*   The main `notes` field is saved to the `moments` table as usual.
*   The content of each perspective field is saved into the `moment_translations` table, linking it to the correct moment and entity.
*   In the rare case there is no perspective, don't save anything to TABLE `moment_translations`

Step 4b: :DONE: when loading an existing Moment page, use the translations for each perspective and only fill in via JS if the translation doesn't exist.


### Step 5: :DONE: Update the Frontend Site Generator
The static site generator (`/admin/scripts/generate_static_site.php`) must be modified.

*   The `Domain\HasMoments` trait will be updated. The `loadMoments()` function will be changed to accept the current perspective (e.g., the Worker or Part object for the page being generated).
*   When fetching moments, the query will `JOIN` with the `moment_translations` table to pull the `translated_note` that matches the current perspective.
*   The `Moment` object will be hydrated with this translated note instead of the internal one.

### Step 6: Content Population
This is a manual data entry phase. All existing Moments will need to be updated one-by-one using the new admin interface to create their perspective-based translations.

### Step 7: AI-Assisted Generation (Future Enhancement)
After the core system is built and stable, we can explore adding a feature to the Moment admin page.
*   A button ("Suggest Translations") would call an AI API.
*   It would send the internal note and the list of perspectives (e.g., "Reversible Guy", "Mr Greene", "19th POSS").
*   The prompt would ask the AI to rewrite the sentence from each perspective.
*   The AI's responses would then populate the perspective fields, ready for a human to review, edit, and save.

## Future Enhancement: Significant Moments

To avoid overwhelming users with a complete history for every Worker and Part, we can introduce a way to flag certain moments as being more important. This will allow us to show a curated list of "highlights" by default, with an option to view the full history.

### 1. Naming

Instead of "key moment," we will use the term **"significant moment"**. The corresponding database field will be `is_significant`.

### 2. Implementation Plan

#### Step A: Database Modification
A new boolean column, `is_significant`, will be added to the `moment_translations` table.

*   **Why `moment_translations`?** A moment might be significant from one worker's perspective (e.g., "I built my masterpiece") but trivial from another's (e.g., "I handed that guy a stick"). Placing the flag on the translation, rather than the moment itself, provides this crucial granularity.
*   **Schema Change:** The `moment_translations` table will be altered to include `is_significant TINYINT(1) NOT NULL DEFAULT 0`.

#### Step B: Admin Interface Update
The Moment edit page (`/admin/moments/moment.php`) will be updated.

*   A checkbox labeled "Significant Moment?" will be added next to each dynamically generated perspective field.
*   The `name` of the checkbox will be structured to link it to the perspective, e.g., `perspectives_meta[worker][{id}][is_significant]`.
*   The save logic in `wwwroot/admin/moments/moment.php` will be updated to process this new data and store it in the `moment_translations` table.

#### Step C: Frontend Query Update
The `loadMoments()` function in `Domain/HasMoments.php` will be modified.

*   It will be updated to accept a new optional boolean parameter, e.g., `loadMoments(?object $perspective, bool $onlySignificant = true)`.
*   By default, it will only load significant moments (`WHERE mt.is_significant = 1`).
*   The frontend generator will be updated to call `loadMoments` twice: once to get the significant moments for the main history, and a second time (passing `false`) to get all moments for a potential "Show Full History" link.

#### Step D: Frontend Template Update
The frontend templates (`part.tpl.php` and `worker.tpl.php`) will be updated to display the curated list and include a link to a separate page containing the full history.
