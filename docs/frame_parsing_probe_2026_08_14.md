# Frame parsing probe — 2026-08-14

Can we read Workers, Parts and events out of the movie's frames?

Feasibility probe on Take 11. Throwaway code; nothing was written to the database,
this repository, or the Dragonframe scene. Tracked as mg issue **#410** (umbrella).

## Answer

**Yes for Workers. No for Part names. Not by parsing every frame.**

| Capability | Result |
|---|---|
| Worker identification @1536px | **97% precision, 79% recall** |
| Worker identification @848px (`feed/hq`) | 100% precision, **36% recall** |
| Part identification | 1 of 38 claims ever confirmed |
| Keyframe reduction | ~8× (11,142 frames → ~1,300 keyframes) |

Ground truth is Rob's own labelling of the same 12 frames — 85 sightings, done blind
to both the Moments and the model output.

## Method

- Take 11 chosen for ground-truth density: 152 Moments, against 47 for Take 10 and 17
  for Take 3. Takes 5–8 have none.
- `take.xml` parsed for the EDL. **1,922 vframes, 178 hidden (`file & 2^30`), 1,744
  played** — verified against the recorded full-movie composition. 335 of the 2,079
  JPEGs on disk (16%) are not in the movie; a directory listing would have been wrong
  by 19% and injected false discontinuities exactly where frames were deleted.
- Model-free diff pass over `feed/hq` (848px, 2.1 ms/frame, no tokens) → change curve →
  keyframes by drift from the last *kept* frame.
- 12 keyframes labelled by 6 independent agents: blind at two resolutions, plus primed
  verification. Blind agents received only a reference sheet, the slug vocabularies and
  the frames; ground truth was held outside their reach.

## Findings that changed the design

### 1. Part slugs encode history, not appearance

`twenty-second-placed-outer-spiral-support` records *when it was placed*. No photograph
contains that. The only part ever confirmed was `el-banderin` — the one that is visually
distinctive *and* not ordinally named.

Part identity is recoverable only from a **change sequence**: the Nth support to appear
is the Nth-placed one. The models already reach for this unprompted — one tracked the
`paul` beam across 8 frames and 700 vframes (leaning → suspended → raised → planted →
"the standing post"), capturing the whole installation while marking every `paul` claim
*unsure*. It got the event right and the name wrong.

### 2. Moments record attention, not presence

Moments name ~5.2 Workers across all spanning rows; ~7.1 are actually on stage. When the
blind arm named Workers the Moments omit, it was right — 16 of 18 such "errors" were
independently confirmed visible.

**The database cannot serve as visibility ground truth.** The census the pipeline
produces is complementary, not redundant: "who was on stage at vframe 1335" is currently
unanswerable.

### 3. Moment ranges open before the subject arrives

Four of four flagged claims confirmed by human check — the DB asserts a Worker over
frames where they are verifiably off-camera (`vf1045`, `vf1304` ×2, `vf1522`).

**Decision (Rob, 2026-08-14):** record the exact visible span; move the viewer's lead-in
to render-time padding. Record what is true; let rendering decide what is shown.

### 4. Recall is two confusable figures, not a ceiling

18 misses across 85 sightings, and `reversible-guy` + `squarehead` are 56% of them —
both rainbow-headed, distinguished by a body that is usually occluded. Five Workers were
never missed once. Discriminator text now lives in memory and belongs in the prompt.

## Predictions, scored

Registered before the agents ran.

| Prediction | Outcome | |
|---|---|---|
| ~100× keyframe reduction | ~8× | wrong |
| Blind worker recall 65–80% with references | 79% | held |
| Ordinal part naming under 10% | 0% | held |
| Primed verification 75–90% | 69% | close |
| Resolution makes little difference | 36% → 79% recall | wrong |

## Prompt design that produced the 97%

Give the slug vocabulary; require an `evidence` string and a confidence per claim;
provide an explicit `unidentified` bucket; state that empty lists are a correct answer.
Zero out-of-vocabulary slugs were invented across all six agents.

A census naming 5 of 7 correctly is useful. One naming 7 of 7 with two wrong is worse —
then every row needs checking. **Never guess** is the rule that matters.

## Fixed along the way

`b.robnugen.com/art/marble_track_3/workers/{2017,2018,2019}/` were returning Apache 403,
making every 2018-era studio portrait in the database a dead public link. Rob fixed the
directory permissions.

## Next

See mg project 22, issue #410 and its children — four phases: reliable identification,
sequence/event extraction, census + Moment correction, then zoom-cropped Moment videos.

The zoom-crop goal depends on a capability this probe did **not** measure: localisation.
Naming a Worker at 97% precision does not say where they are in pixels. That probe
(#415) blocks the rest of Phase 4.
