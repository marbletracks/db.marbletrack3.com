# Realtime Moments Feature

## 1. Goal

The "Realtime Moments" page is a new feature designed to streamline the creation of `Moments` directly within the website's admin panel. It provides a digital-first alternative to the existing workflow of transcribing handwritten `Tokens` from a physical notebook. This allows for faster, more direct logging of `Worker` activities as they are being planned or animated.

## 2. Core Concepts & Data Flow

- **Tokens:** The fundamental units of text. These are fetched from the database and displayed for each worker.
- **Permanent Tokens:** Some tokens, typically the worker's name, can be marked as "permanent". These tokens will always remain available and are not consumed when a moment is created.
- **Moments:** A `Moment` is the final, structured record of an event, created from a `Phrase`.
- **Phrases:** A `Phrase` is a sequence of `Tokens` that are manually assembled by the user to form a complete thought or action.

Per business-rules, all Phrases have a Moment, but not all Moments have Phrases.  Therefore the Moment record must be created before the Phrase can be created.

The data flow is as follows:
1.  `Tokens` are fetched for each `Worker`.
2.  The user drags and drops `Tokens` to build a `Phrase`.
3.  Clicking `[Create Moment]` converts the `Phrase` into a `Moment`.
4.  The `Tokens` used in the `Phrase` (unless permanent) are then hidden from the "Available Tokens" list.

todo:  Allow user to see and edit the Moment before it's saved to disk.  Use a copy of the interface used on Moment edit page: (Alias expand into shortcodes, fields for `moment_translations` per perspective are editable.)

## 3. Page Design and Functionality

The page is located at `wwwroot/admin/moments/realtime.php` and titled "Realtime Moments".

### Worker Sections
The page is divided into sections, one for each `Worker`. Each section contains:
- **Recent Activity:** A list of the two most recent `Moments` for that worker, for context.
- **Available Tokens:** A container holding all unused `Tokens` for that worker.
- **Build-a-Phrase:** A drop zone where `Tokens` can be dragged to construct a new `Phrase`.

### Interaction
1.  **Drag and Drop:** Users can drag tokens from the "Available Tokens" container into the "Build-a-Phrase" container. The order of tokens in the phrase can be adjusted by dragging them within the "Build-a-Phrase" container.
2.  **Toggle Permanence:** A single, quick click on a token (without dragging) will toggle its `is_permanent` status. Permanent tokens are visually distinguished with a solid black border. This action is saved immediately via an AJAX call.
3.  **Moment Creation:**
    -   A `[Create Moment]` button is located next to the "Build-a-Phrase" container.
    -   When clicked, the sequence of tokens in the container is sent to the server.
    -TODO: When `[Create Moment]` button is clicked, make visible an interface for creating Moments based on the Moment text.  Code is based on Moments' moment.tpl.php page
    -TODO: add a new button to actually `[Save Moment and translations]`
    -   The server creates a new `Moment` and a corresponding `Phrase` record. (TODO: and create corresponding `moment_translations`)
    -   The page then reloads, and the tokens used in the new phrase (except for permanent ones) are no longer shown in the "Available Tokens" list.

### Intelligent Moment Parsing
- **Frame Numbers:** If the constructed phrase ends with two numbers (e.g., `... 348 - 381` or `... 348 ~ 381` or `... 348 381`), these numbers are automatically parsed and saved as the `frame_start` and `frame_end` for the `Moment`. The numbers are excluded from the final `moment.notes`.
- **Moment Date:** The `moment_date` is automatically set from the `token_date` of the *last* token in the phrase. If the last token has no date, the current date is used as a fallback.
-TODO: this parsing should be done when "into" the new interface for visually creating the moment on Realtime Moments page as on Moment edit page.
- **Alias Expansion:** Worker and Part aliases (e.g., `GC` or `CS`) are automatically expanded into their full shortcode equivalents (e.g., `[worker:g-choppy]` or `[part:caret-splitter]`) in the final `moment.notes`. The original, un-expanded text is saved in the `phrases.phrase` field for archival purposes.
