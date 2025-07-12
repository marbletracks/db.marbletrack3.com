# Discussion: Improving the "History" Section Display

This document outlines a proposal for improving how "Moments" are displayed in the "History" section on Worker and Part pages.

## 1. The Problem: Redundant Information

Currently, when viewing the history on a specific Worker's page, the Worker's name is repeated in every single moment description. This leads to a repetitive and less readable user experience.

For example, on **Reversible Guy's** page, the history currently looks like this:

**Current Output:**
```
History

* Reversible Guy place and test Caret Splitter Backboard
* Reversible Guy place Caret Splitter Inner Wall
* Reversible Guy get stick and place 2poss
* RG drop stick as 3poss (page 1.5)
* Reversible Guy get 2l2l2b
* Reversible Guy put 2l2l2b on 1l2l2b
* Reversible Guy get 19poss debris from Mr Greene
* Reversible Guy hold 19poss for G Choppy
```

The same issue would apply to Part pages, where the Part's name might be needlessly repeated.

## 2. Proposed Solution: Context-Aware Moment Hydration

The core idea is to make the `Moment` object "smarter." It should be aware of the context (i.e., the page) it's being displayed on. When the list of moments is prepared for a specific Worker or Part, we should process the description to remove the redundant entity name.

This would result in a much cleaner and more intuitive display.

**Desired Output (on Reversible Guy's page):**
```
History

* place and test Caret Splitter Backboard
* place Caret Splitter Inner Wall
* get stick and place 2poss
* drop stick as 3poss (page 1.5)
* get 2l2l2b
* put 2l2l2b on 1l2l2b
* get 19poss debris from Mr Greene
* hold 19poss for G Choppy
```

## 3. Implementation Ideas

We can achieve this by modifying how moments are loaded and prepared.

### Option A: Modify the `HasMoments` Trait

We could enhance the `loadMoments()` function within the `Domain\HasMoments` trait.

1.  The `loadMoments()` method could accept an optional context, such as the name or alias of the parent entity (e.g., "Reversible Guy").
2.  Inside the loop that hydrates each moment, it would strip the provided name from the `notes` property.
3.  This could be done with a case-insensitive search-and-replace for the entity's name and known aliases (e.g., "Reversible Guy", "RG").

This approach keeps the logic centralized in the domain layer.

### Option B: Create a `getDisplayName()` Method on the `Moment` Class

A cleaner approach might be to add a new method to the `Media\Moment` class.

```php
// In Media/Moment.php
public function getContextualNotes(?object $context = null): string
{
    if ($context === null || empty($this->notes)) {
        return $this->notes ?? '';
    }

    $nameToRemove = '';
    $aliasesToRemove = [];

    if ($context instanceof \Physical\Worker) {
        $nameToRemove = $context->name;
        $aliasesToRemove[] = $context->worker_alias;
        // could add more known aliases here
    } elseif ($context instanceof \Physical\Part) {
        $nameToRemove = $context->name;
        $aliasesToRemove[] = $context->part_alias;
    }

    // Logic to remove the name and aliases from the start of the notes string
    $processedNotes = $this->notes;
    // ... implementation ...

    return $processedNotes;
}
```

The template would then call `$moment->getContextualNotes($worker)` instead of accessing `$moment->notes` directly.

### Option C: Perspective-Based Translations

This solution expands the concept of "translation" to be perspective-based. Instead of a single `notes` field, we would generate different descriptions of the same moment depending on which entity's page is being viewed.

The `moments.notes` field would contain a concise, internal description, rich with shortcodes (e.g., `[worker:rg] received [part:19poss] from [worker:mrg]`).

A `moment_translations` table would then store different narrative versions of that event.

**Example `moment_translations` structure:**
*   `moment_id`
*   `perspective_entity_id` (e.g., the ID of the worker or part)
*   `perspective_entity_type` (e.g., 'worker' or 'part')
*   `translated_note` (the narrative text for that perspective)

**Example Workflow:**

1.  **Base Moment Note:** `[worker:rg] received [part:19poss] from [worker:mrg]`
2.  **Translations Generated:**
    *   **For Reversible Guy (RG):** "received 19th Placed Outer Spiral Support from Mr Greene"
    *   **For Mr Greene (MrG):** "gave 19th Placed Outer Spiral Support to Reversible Guy"
    *   **For 19th POSS (19poss):** "was given to Reversible Guy by Mr Greene"

When rendering the history for a specific page (e.g., Mr Greene's worker page), the system would query for the translation corresponding to Mr Greene's ID.

**Advantages:**
*   **Highly Flexible:** Allows for completely different sentence structures and tones, not just removing a name.
*   **Accurate:** Provides the most contextually accurate and natural-sounding history.
*   **Leverages Shortcodes:** Integrates perfectly with the planned use of shortcodes in moment notes.
*   **Maintainable:** Translations can be edited or regenerated as needed.

**Considerations:**
*   This is the most complex solution, requiring a new database table and logic to generate and manage the translations.
*   Since no moment translations exist yet, the database schema can be designed cleanly for this purpose.

## 4. Further Considerations

This concept can be extended.

*   **On a Part's page:** If the moment is "Reversible Guy placed Caret Splitter Backboard", and we are on the "Caret Splitter Backboard" page, the note could be transformed to focus on the actor: "**Reversible Guy** placed and tested this part."
*   **Complex Notes:** For notes like "Reversible Guy get 19poss debris from Mr Greene", if we are on Mr Greene's page, the ideal output might be "Gave 19poss debris to Reversible Guy." This implies a much more sophisticated parsing of the `notes` field, potentially identifying subject, verb, and object. This is likely a future enhancement.

For now, simply stripping the redundant name of the page's subject would be a significant improvement.

```