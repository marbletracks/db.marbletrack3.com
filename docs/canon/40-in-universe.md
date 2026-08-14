# The In-Universe World

## Status
In progress — interviews with Rob, 2026-08-14 onward. Quotes are his exact words.
These are the **rules of the fiction**. Anything written in a Worker's voice —
perspectives in the DB, Patreon posts, website copy — must obey them.

---

## Rule 1: Rob does not exist in their universe

> "In their universe I don't exist. They don't notice anything about how they move."

No fourth wall, ever. The Workers have **no awareness of being animated**. They do
not experience being placed, lifted, or repositioned; they simply move. There is no
hand, no camera, no animator, and no acknowledgement of any of it from inside.

This is stricter than it first sounds. It rules out:

- A Worker reacting to being moved, posed, or knocked over by the cover.
- Any nod, wink, or knowing aside to the audience.
- Workers being aware of the frames, the film, or the passage of production time.

It does **not** rule out Rob narrating his own presence from *outside* — the website
voice does this constantly, keeping both worlds in one paragraph without letting
them touch.

## Rule 2: No time passes for them

> "In the universe I'm creating, no time passed for them even if a year passes
> for me."

The nine-month gaps, the three-year hiatus, the hour spent drilling between two
frames — none of it exists inside the film. Their world is continuous. A raised arm
is raised for as long as the film says it is, and no longer.

**Corollary:** the cover knocking characters out of position is a *production*
problem, not a story event. Restoring them to their marks is not a scene.

## Rule 3: This is not Toy Story

> "Pixar has made Toy Story and I'm not even considering trying."

Explicitly **not** the secret-life-of-objects genre. The Workers do not have a
hidden existence that resumes when Rob leaves the room. There is no off-camera life
to speculate about, and Patreon posts should not invent one.

## Rule 4: The theme park is canon

Decided 2026-08-14. The framing built into the frontend in April 2026 — *"the
ultimate gravity-powered theme park for marbles,"* with **Rides**, **Our Crew**, and
**Residents** — is not merely website copy. It is the truth of the world.

> "I think the theme park should be canon. I mean, the whole project is so silly
> that we might as well make it make sense."

That sentence is the project's governing design principle, and it reaches past this
one decision: **given absurd premises, follow them rigorously.** Silliness is not a
licence for incoherence; it's the reason coherence is worth the trouble.

What it settles:

- The Workers are **crew**. The marbles are **residents and guests**.
- The track is **public infrastructure** — a park being built for a population.
- Pinky isn't poking at a mechanism; she's running **safety inspections before the
  ride opens**. Her "uh oh, small marbles can fall off" is a safety finding.
- Super Spoony isn't hauling cargo; he's **escorting residents**, which is why he
  knows all their names.
- The marbles' first-person DB descriptions (Three Tigers: *"We are three small
  orange marbles with a lot of energy and mobility!"*) are canon voices, not
  decoration.

## Where the making-of goes: commentary, not fiction

The real labour is told happily — just never *inside* the story. Rob's example:

> "I do happily tell people that it took 1 hour between frames when François kicks
> the top stage into place. During that hour I had to lift the entire operation,
> drill a hole in the bottom stage (I think 🤔), screw in four screws which was very
> hard because there's almost no space between the stages, and replace everything
> into the exact spot, which was utterly impossible for me — so it's clear to see
> that something moved."

Two frames, one hour, four screws, and a visible jump because putting it back
*exactly* was impossible. Note that the imperfection is not hidden; it's offered.

**This is the model for a Patreon post.** In-universe: François kicks the upper base
into place, exactly centred, so it can rotate. Out-of-universe: an hour of drilling
under the stage, and you can see the seam if you look. The two are told side by side
and never mixed — which is exactly what the website voice already does.

Cross-reference: François's `description` field in the database (worker_id 10,
verified via `GET /api/v1/workers/francois/`) reads *"I brought the upper base into
place. I kicked it to be exactly centered so it could rotate properly. You are
welcome."* Text only — there is no audio anywhere in the project. He has no idea
about the screws. He never will.

## Milestone candidate

**François kicks the upper base into place** — one kick, one hour, four screws, a
visible drift between two frames. Strong candidate for the milestone list; it
demonstrates the whole two-worlds structure in a single beat.

## Open questions

- Do the Workers know they're building a **theme park**, or just a track?
- Who **commissioned** it? Is there an owner or authority above Doctor Sugar, or is
  the crew self-directing? (Rob confirms Doctor Sugar manages and knows where every
  piece goes, and that Candy Mama likewise "knows everything.")
- Are the marbles **already resident** while it's under construction, or do they
  arrive when it opens? The frontend says "Residents," which implies they live there
  now — in a park that isn't finished.
- Are the Workers paid? Do they live there too?
- How does a **new Worker arrive** in-universe? Ms McGlue "came to help when he fell
  behind schedule" — who hired her?
- Do Workers age, retire, or leave? Y Slider is "no longer with the crew."
- Is there any plot beyond construction, or is the build itself the story?
