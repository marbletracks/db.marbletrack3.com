# Realtime Moments Feature

## 1. Goal

The "Realtime Moments" page is a new feature designed to streamline the creation of `Moments` directly within the website's admin panel. It provides a digital-first alternative to the existing workflow of transcribing handwritten `Tokens` from a physical notebook. This allows for faster, more direct logging of `Worker` activities as they are being planned or animated.

## 2. Core Concepts & Data Flow

This feature introduces a new page that acts as a digital notebook. The text entered on this page is saved immediately as `Tokens`, which are then grouped into `Phrases`. These `Phrases` are then used to generate `Moments`.

- **From Physical to Digital:** Instead of writing in the notebook and later transcribing, this page allows for the direct creation of `Tokens`.
- **Immediate Token Creation:** Any text entered is immediately saved as a `Token` in the database.
- **Phrases Group Tokens:** A `Phrase` is a collection of one or more `Tokens` that logically belong together. On this page, each line of input under a worker will correspond to a single `Phrase`.
- **Moments Expand Phrases:** A `Moment` is the structured, actionable version of a `Phrase`, containing frame data and expanded shortcodes.
- **Linking Phrases to Workers (Temporary Solution):** To associate an incomplete `Phrase` (one that is not yet a `Moment`) with a `Worker`, a temporary column `temp_not_3rd_normal_worker_id` will be added to the `phrases` table. This provides the necessary link for the UI.

## 3. Page Design and Functionality

The new page will be located at `wwwroot/admin/moments/realtime.php` and titled "Realtime Moments".

### Step 1: Worker Grid
The page will display a sortable grid of all `Workers` who are the main drivers of activity. The sorting order will be determined by the `worker.busy_sort` database column.

### Step 2: Display of Recent Activity
Below each `Worker` in the grid, a list of their activity will be displayed:
- **Incomplete Phrases:** Open `Phrases` linked to that `Worker` via `temp_not_3rd_normal_worker_id`. These are lines of `Tokens` waiting to become a `Moment`.
- **Recent Moments:** A list of the most recent `Moments` for which a `moment_translation` exists for that `Worker`.
- The sorting will be chronological (`moment.take_id`, `moment.frame_start`).

### Step 3: Input and Moment Creation

1.  **Input:** A text input field will be available under each `Worker`'s activity list. This represents a new, empty `Phrase`.
2.  **Token Creation:** When a user types text (e.g., `Go to CS 1245`) and submits (e.g., presses Enter or a save button), a `Token` is created instantly.
3.  **Phrase Creation/Update:** A corresponding `Phrase` record is created or updated.
    -   The `phrase.token_json` array is populated with the new `token_id`.
    -   The `phrase.temp_not_3rd_normal_worker_id` is set to the ID of the `Worker` it's under.
4.  **Realtime Parsing:** As `Tokens` are added, a read-only field below the input will show a preview of the potential `Moment.note` (e.g., `[worker:reversible-guy] go to [part:caret-splitter]`) and the frame numbers, using the same parsing logic as the moment edit page.
5.  **Moment Creation:** A `[create moment]` button is always visible next to each `Phrase`.
    -   **Heuristic:** The button could be enabled or highlighted once the system detects what looks like a complete phrase (e.g., text and two numbers).
    -   **Action:** When clicked, the system uses the `Tokens` in the `Phrase` to create a new `Moment` and its associated `moment_translations`. The original `Tokens` and `Phrase` remain in the database for archival purposes.
    -   After creation, a new empty `Phrase` input appears for that worker.

### Step 4: Handling Planned (Incomplete) Moments
The system inherently supports planned `Moments`. These are simply `Phrases` that have `Tokens` but have not yet had the `[create moment]` button clicked. They remain visible under their associated `Worker` on each page load.

**Example of planned `Phrase`:**
A user has entered two `Tokens` on separate occasions for the same `Phrase`:
- `Token 1`: "Hold CSR"
- `Token 2`: "1350"

This will be displayed as an incomplete `Phrase` ("Hold CSR 1350") until the user adds a final frame number and clicks `[create moment]`.