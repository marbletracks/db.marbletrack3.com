-- Core parts table
CREATE TABLE parts (
  part_id INT AUTO_INCREMENT PRIMARY KEY,
  part_alias VARCHAR(20) NOT NULL UNIQUE
) COMMENT='Each part is a distinct section of the marble track, identified by a URL-safe alias.';

-- Replaces VSCode snippets
CREATE TABLE shortcodes (
  shortcode_id INT PRIMARY KEY AUTO_INCREMENT,
  keyword VARCHAR(50) NOT NULL,
  language CHAR(2) NOT NULL DEFAULT 'en',
  replacement VARCHAR(255) NOT NULL,
  context SET('parts', 'workers') NOT NULL,
  UNIQUE(keyword, language)
) COMMENT='Replaces VSCode snippets.';

INSERT INTO shortcodes (keyword, language, replacement, context)
VALUES
("auto", "en", "[Autosticks](/workers/autosticks/)", "workers"),
("big", "en", "[Big Brother](/workers/big_brother/)", "workers"),
("bpj", "en", "[Backpack Jack](/workers/backpack_jack/)", "workers"),
("cm", "en", "[Candy Mama](/workers/candy_mama/)", "workers"),
("ds", "en", "[Doctor Sugar](/workers/dr_sugar/)", "workers"),
("gar", "en", "[Garinoppi](/workers/garinoppi/)", "workers"),
("gc", "en", "[G Choppy](/workers/g_choppy/)", "workers"),
("lil", "en", "[Little Brother](/workers/lil_brother/)", "workers"),
("mmg", "en", "[Mr McGlue](/workers/mr_mcglue/)", "workers"),
("mrg", "en", "[Mr Greene](/workers/mr_greene/)", "workers"),
("rg", "en", "[Reversible Guy](/workers/reversible/)", "workers"),
("pink", "en", "[Pinky](/workers/pinky/)", "workers"),
("rab", "en", "[Rabby](/workers/rabby/)", "workers"),
("square", "en", "[Squarehead](/workers/squarehead/)", "workers"),
("sup", "en", "[Super Spoony](/workers/super_spoony/)", "workers"),
("0poss", "en", "[zeroth_placed_outer_spiral_support](/parts/zeroth_placed_outer_spiral_support/)", "parts"),
("1poss", "en", "[First Placed Outer Spiral Support](/parts/001p_first_placed_outer_spiral_support/)", "parts"),
("2poss", "en", "[Second Placed Outer Spiral Support](/parts/002p_second_placed_outer_spiral_support/)", "parts"),
("3poss", "en", "[Third Placed Outer Spiral Support](/parts/003p_third_placed_outer_spiral_support/)", "parts"),
("4poss", "en", "[Fourth Placed Outer Spiral Support](/parts/004p_fourth_placed_outer_spiral_support/)", "parts"),
("5poss", "en", "[Fifth Placed Outer Spiral Support](/parts/005p_fifth_placed_outer_spiral_support/)", "parts"),
("6poss", "en", "[Sixth Placed Outer Spiral Support](/parts/006p_sixth_placed_outer_spiral_support/)", "parts"),
("7poss", "en", "[Seventh Placed Outer Spiral Support](/parts/007p_seventh-placed-outer-spiral-support/)", "parts"),
("8poss", "en", "[Eighth Placed Outer Spiral Support](/parts/008p_eighth-placed-outer-spiral-support/)", "parts"),
("9poss", "en", "[Ninth Placed Outer Spiral Support](/parts/009p_ninth-placed-outer-spiral-support/)", "parts"),
("10poss", "en", "[Tenth Placed Outer Spiral Support](/parts/tenth-placed-outer-spiral-support/)", "parts"),
("11poss", "en", "[Eleventh placed Outer Spiral Support](/parts/eleventh-placed-outer-spiral-support/)", "parts"),
("12poss", "en", "[Twelfth placed Outer Spiral Support](/parts/twelfth-placed-outer-spiral-support/)", "parts"),
("13poss", "en", "[Thirteenth Placed Outer Spiral Support](/parts/thirteenth-placed-outer-spiral-support/)", "parts"),
("14poss", "en", "[Fourteenth Placed Outer Spiral Support](/parts/fourteenth-placed-outer-spiral-support/)", "parts"),
("15poss", "en", "[Fifteenth Placed Outer Spiral Support](/parts/fifteenth-placed-outer-spiral-support/)", "parts"),
("16poss", "en", "[Sixteenth Placed Outer Spiral Support](/parts/sixteenth-placed-outer-spiral-support/)", "parts"),
("17poss", "en", "[Seventeenth Placed Outer Spiral Support](/parts/seventeenth-placed-outer-spiral-support/)", "parts"),
("18poss", "en", "[Eighteenth Placed Outer Spiral Support](/parts/eighteenth-placed-outer-spiral-support/)", "parts"),
("19poss", "en", "[Nineteenth Placed Outer Spiral Support](/parts/nineteenth-placed-outer-spiral-support/)", "parts"),
("20poss", "en", "[Twentieth Placed Outer Spiral Support](/parts/twentieth-placed-outer-spiral-support/)", "parts"),
("2bbl1", "en", "[2nd bridge bridge level 1](/parts/2nd-bridge-bridge-level-1/)", "parts"),
("2bl1", "en", "[Second Bridge Level 1](/parts/second-bridge-level-1/)", "parts"),
("2bs", "en", "[Second Bridge Supports](/parts/second-bridge-supports/)", "parts"),
("3rda", "en", "[The Third Arch](/parts/the-third-arch/)", "parts"),
("bbcbcs", "en", "[Bar Below Circle by Caret Splitter](/parts/bar_below_circle_by_the_caret_splitter/)", "parts"),
("bearing", "en", "[Bearing](/parts/bearing/)", "parts"),
("blzzut", "en", "[Below Lower Zig Zag U-turn](/parts/below-lower-zig-zag-u-turn/)", "parts"),
("brmlz", "en", "[Back Right Medium Landing Zone](/parts/back_right_medium_landing_zone/)", "parts"),
("cbtcs", "en", "[Circle by the Caret Splitter](/parts/circle_by_the_caret_splitter/)", "parts"),
("cs", "en", "[Caret Splitter](/parts/caret-splitter/)", "parts"),
("csb", "en", "[Caret Splitter Backboard](/parts/caret-splitter-backboard/)", "parts"),
("csf", "en", "[Caret Splitter Feeder](/parts/caret_splitter_feeder/)", "parts"),
("csfe", "en", "[Caret Splitter Feeder Extension](/parts/caret_splitter_feeder_extension/)", "parts"),
("csfer", "en", "[Caret Splitter Feeder Extension Reflection](/parts/caret-splitter-feeder-extension-reflection/)", "parts"),
("csfer2s", "en", "[Caret Splitter Feeder Extension Reflection Second Stanchion](/parts/caret-splitter-feeder-extension-reflection-second-stanchion/)", "parts"),
("csferllp", "en", "[Caret Splitter Feeder Extension Reflection Lower Left Protection](/parts/caret-splitter-feeder-extension-reflection-lower-left-protection/)", "parts"),
("csferlrp", "en", "[Caret Splitter Feeder Extension Reflection Lower Right Protection](/parts/caret-splitter-feeder-extension-reflection-lower-right-protection/)", "parts"),
("csferp", "en", "[Caret Splitter Feeder Extension Reflection Protection](/parts/caret-splitter-feeder-extension-reflection-protection/)", "parts"),
("csferph", "en", "[Caret Splitter Feeder Extension Reflection Protection Holders](/parts/caret-splitter-feeder-extension-reflection-protection-holders/)", "parts"),
("csferplh", "en", "[Caret Splitter Feeder Extension Reflection Protection Lower Holder](/parts/caret-splitter-feeder-extension-reflection-protection-lower-holder/)", "parts"),
("csferpuh", "en", "[Caret Splitter Feeder Extension Reflection Protection Upper Holder](/parts/caret-splitter-feeder-extension-reflection-protection-upper-holder/)", "parts"),
("csfers", "en", "[Caret Splitter Feeder Extension Reflection Stanchion](/parts/caret-splitter-feeder-extension-reflection-stanchion/)", "parts"),
("csfg", "en", "[Caret Splitter Feeder Guider](/parts/caret-splitter-feeder-guider/)", "parts"),
("csfgls", "en", "[Caret Splitter Feeder Guider Lower Support](/parts/caret-splitter-feeder-guider-lower-supporter/)", "parts"),
("csfgus", "en", "[Caret Splitter Feeder Guider Upper Supporter](/parts/caret-splitter-feeder-guider-upper-supporter/)", "parts"),
("csflg", "en", "[Caret Splitter Feeder Lefter Guider](/parts/caret-splitter-feeder-lefter-guider/)", "parts"),
("csfrg", "en", "[Caret Splitter Feeder Righter Guider](/parts/caret-splitter-feeder-righter-guider/)", "parts"),
("csl", "en", "[Caret Splitter Lever](/parts/caret-splitter-lever/)", "parts"),
("csr", "en", "[Caret Splitter Rudder](/parts/caret-splitter-rudder/)", "parts"),
("csrhis", "en", "[Caret Splitter Right Hand Inner Side](/parts/caret-splitter-right-hand-inner-side/)", "parts"),
("csrhsig", "en", "[Caret Splitter Right Hand Side Inner Guider](/parts/caret-splitter-right-hand-side-inner-guider/)", "parts"),
("csrhsog", "en", "[Caret Splitter Right Hand Side Outer Guider](/parts/caret-splitter-right-hand-side-outer-guider/)", "parts"),
("csrls", "en", "[Caret Splitter Rudder Lever Spinner](/parts/caret-splitter-rudder-lever-spinner/)", "parts"),
("csrt", "en", "[Caret Splitter Right Track](/parts/caret_splitter_right_track/)", "parts"),
("dc48", "en", "[DC 48](/parts/dc-48/)", "parts"),
("dwatlsms", "en", "[Decorative Walls after the Lowest Small-Medium Splitter](/parts/decorative_walls_after_the_lowest_small-medium_splitter/)", "parts"),
("eLL", "en", "[el Lifty Lever](/parts/el-lifty-lever/)", "parts"),
("eb", "en", "[El Bander\u00edn](/parts/el-bander\u00edn/)", "parts"),
("ellg", "en", "[el Lifty Lever Guider](/parts/el-lifty-lever-guider/)", "parts"),
("flmlz", "en", "[Front Left Medium Landing Zone](/parts/front_left_medium_landing_zone/)", "parts"),
("fsir", "en", "[First Snake-Installed Rail](/parts/first_snake-installed_rail/)", "parts"),
("gibrmlz", "en", "[Guides into Back Right Medium Landing Zone](/parts/guides-into-back-right-medium-landing-zone/)", "parts"),
("jos", "en", "[\u5186\u5468\u87ba\u65cb](/parts/outer_spiral/)", "parts"),
("lblsmc", "en", "[Lowest Back Left Small Marble Curve](/parts/lowest_back_left_small_marble_curve/)", "parts"),
("lcotlsms", "en", "[Lower Curver off the Lowest Small-Medium Splitter](/parts/lower_curver_off_the_lowest_small-medium_splitter/)", "parts"),
("llb", "en", "[Lowest Largest Backtrack](/parts/lowest-largest-backtrack/)", "parts"),
("llbc", "en", "[Lowest Largest Backtracking Chopstick](/parts/lowest_largest_backtracking_chopstick/)", "parts"),
("llbps", "en", "[Lowest Largest Backtracking Popsicle Stick](/parts/lowest_largest_backtracking_popsicle_stick/)", "parts"),
("llsa", "en", "[Lower Left Side Arch](/parts/lower-left-side-arch/)", "parts"),
("llut", "en", "[Lowest Largest U-Turn](/parts/lowest_largest_u_turn/)", "parts"),
("llutb", "en", "[Lowest Largest U-turn Berm](/parts/lowest-largest-u-turn-berm/)", "parts"),
("lsmsitk", "en", "[Lower Small Medium Splitter Inner Thinner Keeper](/parts/lower-small-medium-splitter-inner-thinner-keeper/)", "parts"),
("lsslz", "en", "[Left Side Small Landing Zone](/parts/left_side_small_landing_zone/)", "parts"),
("lwt", "en", "[Little Wiggly Track](/parts/little_wiggly_track/)", "parts"),
("lzz", "en", "[Lower Zig Zag](/parts/lower_zig_zag/)", "parts"),
("lzz1f", "en", "[Lower Zig Zag 1F](/parts/lower-zig-zag-1f/)", "parts"),
("lzz1fir", "en", "[Lower Zig Zag 1F Inner Rails](/parts/lower-zig-zag-1f-inner-rails/)", "parts"),
("lzz2bt", "en", "[Lower Zig Zag 2 ban top](/parts/lower-zig-zag-2-ban-top/)", "parts"),
("lzz2f", "en", "[Lower Zig Zag 2F](/parts/lower-zig-zag-2f/)", "parts"),
("lzz2fb", "en", "[Lower Zig Zag 2F Bouncer](/parts/lower-zig-zag-2f-bouncer/)", "parts"),
("lzz2fg", "en", "[Lower Zig Zag 2F Guardrails](/parts/lower-zig-zag-2f-guardrails/)", "parts"),
("lzz32n", "en", "[Lower Zig Zag 3F 2F Net](/parts/lower-zig-zag-3f-2f-net/)", "parts"),
("lzz3bls", "en", "[Lower Zig Zag 3 ban Lower Support](/parts/lower-zig-zag-3-ban-lower-support/)", "parts"),
("lzz3bub", "en", "[Lower Zig Zag 3 ban Upper Base](/parts/lower-zig-zag-3-ban-upper-base/)", "parts"),
("lzz3bubb", "en", "[Lower Zig Zag 3 ban Upper Base Base](/parts/lower-zig-zag-3-ban-upper-base-base/)", "parts"),
("lzz3f", "en", "[Lower Zig Zag 3F](/parts/lower-zig-zag-3f/)", "parts"),
("lzz3fna", "en", "[Lower Zig Zag 3F Net Affector](/parts/lower-zig-zag-3f-net-affector/)", "parts"),
("lzz3fr", "en", "[Lower Zig Zag 3F Rails](/parts/lower-zig-zag-3f-rails/)", "parts"),
("lzzn", "en", "[Lower Zig Zag Net](/parts/lower_zig_zag_net/)", "parts"),
("lzztssfs", "en", "[Lower Zig Zag Triple Splitter Small Feeder Separator](/parts/lower-zig-zag-triple-splitter-small-feeder-separator/)", "parts"),
("mcbts", "en", "[Medium Catcher Below Triple Splitter](/parts/medium-catcher-below-triple-splitter/)", "parts"),
("os", "en", "[Outer Spiral](/parts/outer_spiral/)", "parts"),
("paul", "en", "[Paul](/parts/paul/)", "parts"),
("rsslz", "en", "[Right Side Small Landing Zone](/parts/right_side_small_landing_zone/)", "parts"),
("scbcs", "en", "[Second Circle Below Caret Splitter](/parts/second-circle-below-caret-splitter/)", "parts"),
("sms", "en", "[Small Marble Saver](/parts/small-marble-saver/)", "parts"),
("sp", "en", "[Snake Plate](/parts/snake_plate/)", "parts"),
("spc", "en", "[Snake Plate Chopstick](/parts/snake_plate_chopstick/)", "parts"),
("sput", "en", "[Snake Plate U Turn](/parts/snake_plate_u_turn/)", "parts"),
("sputb", "en", "[Snake Plate U-Turn Berm](/parts/snake_plate_u_turn_berm/)", "parts"),
("sputbb", "en", "[Snake Plate U-turn Berm Bar](/parts/snake-plate-u-turn-berm-bar/)", "parts"),
("tecots", "en", "[Two Ends Chopped Off Triple Splitter](/parts/two-ends-chopped-off-triple-splitter/)", "parts"),
("tft", "en", "[The First Track](/parts/the_first_track/)", "parts"),
("tga", "en", "[The Grand Archway](/parts/the-grand-archway/)", "parts"),
("tlls", "en", "[The Lowest Longest Separator](/parts/the-lowest-longest-separator/)", "parts"),
("tlsms", "en", "[the Lowest Small-Medium Splitter](/parts/the_lowest_small-medium_splitter/)", "parts"),
("ts", "en", "[Triple Splitter](/parts/triple_splitter/)", "parts"),
("tsmcr", "en", "[Triple Splitter Medium Catcher Ridge](/parts/triple-splitter-medium-catcher-ridge/)", "parts"),
("tssf", "en", "[Triple Splitter Small Feeder](/parts/triple-splitter-small-feeder/)", "parts"),
("tssff", "en", "[Triple Splitter Small Feeder Feeder](/parts/triple-splitter-small-feeder-feeder/)", "parts"),
("tssfib", "en", "[Triple Splitter Small Feeder Inner Bar](/parts/triple-splitter-small-feeder-inner-bar/)", "parts"),
("tssfibls", "en", "[Triple Splitter Small Feeder Inner Bar Lower Support](/parts/triple-splitter-small-feeder-inner-bar-lower-support/)", "parts"),
("tssfig", "en", "[Triple Splitter Small Feeder Inner Guider](/parts/triple-splitter-small-feeder-inner-guider/)", "parts"),
("tssfob", "en", "[Triple Splitter Small Feeder Outer Bar](/parts/triple-splitter-small-feeder-outer-bar/)", "parts"),
("tssfobls", "en", "[Triple Splitter Small Feeder Outer Bar Lower Support](/parts/triple-splitter-small-feeder-outer-bar-lower-support/)", "parts"),
("tssfog", "en", "[Triple Splitter Small Feeder Outer Guider](/parts/triple-splitter-small-feeder-outer-guider/)", "parts"),
("ttssmc", "en", "[the Triple Splitter Small Marble Catcher](/parts/the_triple_splitter_small_marble_catcher/)", "parts"),
("usotlsms", "en", "[Upper Splitter off the Lowest Small-Medium Splitter](/parts/upper_splitter_off_the_lowest_small-medium_splitter/)", "parts"),
("zog", "en", "[Zog](/parts/zog/)", "parts"),
("zogr", "en", "[Zog Rails](/parts/zog-rails/)", "parts"),
("zogrs", "en", "[Zog Rails Support](/parts/zog-rails-support/)", "parts");



-- Translations for part name/description
CREATE TABLE part_translations (
  part_translation_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  language_code CHAR(2) NOT NULL,
  part_name VARCHAR(255),
  part_description TEXT,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- General photos for parts
CREATE TABLE parts_photos (
  part_photo_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  photo_code CHAR(16) NOT NULL,
  friendly_name VARCHAR(255),
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- Gravity-fed connections between parts
CREATE TABLE part_connections (
  connection_id INT AUTO_INCREMENT PRIMARY KEY,
  from_part_id INT NOT NULL,
  to_part_id INT NOT NULL,
  marble_sizes SET('small', 'medium', 'large') NOT NULL,
  connection_description TEXT,
  FOREIGN KEY (from_part_id) REFERENCES parts(part_id) ON DELETE CASCADE,
  FOREIGN KEY (to_part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- Historical moments in a part's life
CREATE TABLE part_histories (
  part_history_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  event_date DATE,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- Translations for part history events
CREATE TABLE part_history_translations (
  part_history_translation_id INT AUTO_INCREMENT PRIMARY KEY,
  part_history_id INT NOT NULL,
  language_code CHAR(2) NOT NULL,
  history_title VARCHAR(255),
  history_description TEXT,
  FOREIGN KEY (part_history_id) REFERENCES part_histories(part_history_id) ON DELETE CASCADE
);

-- Images attached to part history events
CREATE TABLE part_history_photos (
  part_history_photo_id INT AUTO_INCREMENT PRIMARY KEY,
  part_history_id INT NOT NULL,
  photo_sort TINYINT NOT NULL,
  history_photo VARCHAR(255),
  FOREIGN KEY (part_history_id) REFERENCES part_histories(part_history_id) ON DELETE CASCADE
);
