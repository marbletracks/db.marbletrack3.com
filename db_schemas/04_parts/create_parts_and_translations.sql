INSERT INTO parts (part_alias) VALUES ('0poss');
INSERT INTO parts (part_alias) VALUES ('1poss');
INSERT INTO parts (part_alias) VALUES ('2poss');
INSERT INTO parts (part_alias) VALUES ('3poss');
INSERT INTO parts (part_alias) VALUES ('4poss');
INSERT INTO parts (part_alias) VALUES ('5poss');
INSERT INTO parts (part_alias) VALUES ('6poss');
INSERT INTO parts (part_alias) VALUES ('7poss');
INSERT INTO parts (part_alias) VALUES ('8poss');
INSERT INTO parts (part_alias) VALUES ('9poss');
INSERT INTO parts (part_alias) VALUES ('10poss');
INSERT INTO parts (part_alias) VALUES ('11poss');
INSERT INTO parts (part_alias) VALUES ('12poss');
INSERT INTO parts (part_alias) VALUES ('13poss');
INSERT INTO parts (part_alias) VALUES ('14poss');
INSERT INTO parts (part_alias) VALUES ('15poss');
INSERT INTO parts (part_alias) VALUES ('16poss');
INSERT INTO parts (part_alias) VALUES ('17poss');
INSERT INTO parts (part_alias) VALUES ('18poss');
INSERT INTO parts (part_alias) VALUES ('19poss');
INSERT INTO parts (part_alias) VALUES ('20poss');
INSERT INTO parts (part_alias) VALUES ('2bbl1');
INSERT INTO parts (part_alias) VALUES ('2bl1');
INSERT INTO parts (part_alias) VALUES ('2bs');
INSERT INTO parts (part_alias) VALUES ('3rda');
INSERT INTO parts (part_alias) VALUES ('bbcbcs');
INSERT INTO parts (part_alias) VALUES ('bearing');
INSERT INTO parts (part_alias) VALUES ('blzzut');
INSERT INTO parts (part_alias) VALUES ('brmlz');
INSERT INTO parts (part_alias) VALUES ('cbtcs');
INSERT INTO parts (part_alias) VALUES ('cs');
INSERT INTO parts (part_alias) VALUES ('csb');
INSERT INTO parts (part_alias) VALUES ('csf');
INSERT INTO parts (part_alias) VALUES ('csfe');
INSERT INTO parts (part_alias) VALUES ('csfer');
INSERT INTO parts (part_alias) VALUES ('csfer2s');
INSERT INTO parts (part_alias) VALUES ('csferllp');
INSERT INTO parts (part_alias) VALUES ('csferlrp');
INSERT INTO parts (part_alias) VALUES ('csferp');
INSERT INTO parts (part_alias) VALUES ('csferph');
INSERT INTO parts (part_alias) VALUES ('csferplh');
INSERT INTO parts (part_alias) VALUES ('csferpuh');
INSERT INTO parts (part_alias) VALUES ('csfers');
INSERT INTO parts (part_alias) VALUES ('csfg');
INSERT INTO parts (part_alias) VALUES ('csfgls');
INSERT INTO parts (part_alias) VALUES ('csfgus');
INSERT INTO parts (part_alias) VALUES ('csflg');
INSERT INTO parts (part_alias) VALUES ('csfrg');
INSERT INTO parts (part_alias) VALUES ('csl');
INSERT INTO parts (part_alias) VALUES ('csr');
INSERT INTO parts (part_alias) VALUES ('csrhis');
INSERT INTO parts (part_alias) VALUES ('csrhsig');
INSERT INTO parts (part_alias) VALUES ('csrhsog');
INSERT INTO parts (part_alias) VALUES ('csrls');
INSERT INTO parts (part_alias) VALUES ('csrt');
INSERT INTO parts (part_alias) VALUES ('dc48');
INSERT INTO parts (part_alias) VALUES ('dwatlsms');
INSERT INTO parts (part_alias) VALUES ('eLL');
INSERT INTO parts (part_alias) VALUES ('eb');
INSERT INTO parts (part_alias) VALUES ('ellg');
INSERT INTO parts (part_alias) VALUES ('flmlz');
INSERT INTO parts (part_alias) VALUES ('fsir');
INSERT INTO parts (part_alias) VALUES ('gibrmlz');
INSERT INTO parts (part_alias) VALUES ('jos');
INSERT INTO parts (part_alias) VALUES ('lblsmc');
INSERT INTO parts (part_alias) VALUES ('lcotlsms');
INSERT INTO parts (part_alias) VALUES ('llb');
INSERT INTO parts (part_alias) VALUES ('llbc');
INSERT INTO parts (part_alias) VALUES ('llbps');
INSERT INTO parts (part_alias) VALUES ('llsa');
INSERT INTO parts (part_alias) VALUES ('llut');
INSERT INTO parts (part_alias) VALUES ('llutb');
INSERT INTO parts (part_alias) VALUES ('lsmsitk');
INSERT INTO parts (part_alias) VALUES ('lsslz');
INSERT INTO parts (part_alias) VALUES ('lwt');
INSERT INTO parts (part_alias) VALUES ('lzz');
INSERT INTO parts (part_alias) VALUES ('lzz1f');
INSERT INTO parts (part_alias) VALUES ('lzz1fir');
INSERT INTO parts (part_alias) VALUES ('lzz2bt');
INSERT INTO parts (part_alias) VALUES ('lzz2f');
INSERT INTO parts (part_alias) VALUES ('lzz2fb');
INSERT INTO parts (part_alias) VALUES ('lzz2fg');
INSERT INTO parts (part_alias) VALUES ('lzz32n');
INSERT INTO parts (part_alias) VALUES ('lzz3bls');
INSERT INTO parts (part_alias) VALUES ('lzz3bub');
INSERT INTO parts (part_alias) VALUES ('lzz3bubb');
INSERT INTO parts (part_alias) VALUES ('lzz3f');
INSERT INTO parts (part_alias) VALUES ('lzz3fna');
INSERT INTO parts (part_alias) VALUES ('lzz3fr');
INSERT INTO parts (part_alias) VALUES ('lzzn');
INSERT INTO parts (part_alias) VALUES ('lzztssfs');
INSERT INTO parts (part_alias) VALUES ('mcbts');
INSERT INTO parts (part_alias) VALUES ('os');
INSERT INTO parts (part_alias) VALUES ('paul');
INSERT INTO parts (part_alias) VALUES ('rsslz');
INSERT INTO parts (part_alias) VALUES ('scbcs');
INSERT INTO parts (part_alias) VALUES ('sms');
INSERT INTO parts (part_alias) VALUES ('sp');
INSERT INTO parts (part_alias) VALUES ('spc');
INSERT INTO parts (part_alias) VALUES ('sput');
INSERT INTO parts (part_alias) VALUES ('sputb');
INSERT INTO parts (part_alias) VALUES ('sputbb');
INSERT INTO parts (part_alias) VALUES ('tecots');
INSERT INTO parts (part_alias) VALUES ('tft');
INSERT INTO parts (part_alias) VALUES ('tga');
INSERT INTO parts (part_alias) VALUES ('tlls');
INSERT INTO parts (part_alias) VALUES ('tlsms');
INSERT INTO parts (part_alias) VALUES ('ts');
INSERT INTO parts (part_alias) VALUES ('tsmcr');
INSERT INTO parts (part_alias) VALUES ('tssf');
INSERT INTO parts (part_alias) VALUES ('tssff');
INSERT INTO parts (part_alias) VALUES ('tssfib');
INSERT INTO parts (part_alias) VALUES ('tssfibls');
INSERT INTO parts (part_alias) VALUES ('tssfig');
INSERT INTO parts (part_alias) VALUES ('tssfob');
INSERT INTO parts (part_alias) VALUES ('tssfobls');
INSERT INTO parts (part_alias) VALUES ('tssfog');
INSERT INTO parts (part_alias) VALUES ('ttssmc');
INSERT INTO parts (part_alias) VALUES ('usotlsms');
INSERT INTO parts (part_alias) VALUES ('zog');
INSERT INTO parts (part_alias) VALUES ('zogr');
INSERT INTO parts (part_alias) VALUES ('zogrs');

INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Back Right Medium Landing Zone', 'Back Right Medium Landing Zone' FROM parts WHERE part_alias = 'brmlz';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Bar Below Circle by Caret Splitter', 'Bar Below Circle by Caret Splitter' FROM parts WHERE part_alias = 'bbcbcs';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Bearing', 'Bearing' FROM parts WHERE part_alias = 'bearing';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Backboard', 'Caret Splitter Backboard' FROM parts WHERE part_alias = 'csb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Second Stanchion', 'Caret Splitter Feeder Extension Reflection Second Stanchion' FROM parts WHERE part_alias = 'csfer2s';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Stanchion', 'Caret Splitter Feeder Extension Reflection Stanchion' FROM parts WHERE part_alias = 'csfers';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection', 'Caret Splitter Feeder Extension Reflection' FROM parts WHERE part_alias = 'csfer';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension', 'Caret Splitter Feeder Extension' FROM parts WHERE part_alias = 'csfe';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder', 'Caret Splitter Feeder' FROM parts WHERE part_alias = 'csf';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Right Hand Inner Side', 'Caret Splitter Right Hand Inner Side' FROM parts WHERE part_alias = 'csrhis';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Right Track', 'Caret Splitter Right Track' FROM parts WHERE part_alias = 'csrt';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Rudder', 'Caret Splitter Rudder' FROM parts WHERE part_alias = 'csr';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter', 'Caret Splitter' FROM parts WHERE part_alias = 'cs';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Circle by the Caret Splitter', 'Circle by the Caret Splitter' FROM parts WHERE part_alias = 'cbtcs';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Decorative Walls after the Lowest Small-Medium Splitter', 'Decorative Walls after the Lowest Small-Medium Splitter' FROM parts WHERE part_alias = 'dwatlsms';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Eighth Placed Outer Spiral Support', 'Eighth Placed Outer Spiral Support' FROM parts WHERE part_alias = '8poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Fifth Placed Outer Spiral Support', 'Fifth Placed Outer Spiral Support' FROM parts WHERE part_alias = '5poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'First Placed Outer Spiral Support', 'First Placed Outer Spiral Support' FROM parts WHERE part_alias = '1poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'First Snake-Installed Rail', 'First Snake-Installed Rail' FROM parts WHERE part_alias = 'fsir';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Fourth Placed Outer Spiral Support', 'Fourth Placed Outer Spiral Support' FROM parts WHERE part_alias = '4poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Front Left Medium Landing Zone', 'Front Left Medium Landing Zone' FROM parts WHERE part_alias = 'flmlz';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Guides into Back Right Medium Landing Zone', 'Guides into Back Right Medium Landing Zone' FROM parts WHERE part_alias = 'gibrmlz';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Left Side Small Landing Zone', 'Left Side Small Landing Zone' FROM parts WHERE part_alias = 'lsslz';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Little Wiggly Track', 'Little Wiggly Track' FROM parts WHERE part_alias = 'lwt';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Curver off the Lowest Small-Medium Splitter', 'Lower Curver off the Lowest Small-Medium Splitter' FROM parts WHERE part_alias = 'lcotlsms';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 1F', 'Lower Zig Zag 1F' FROM parts WHERE part_alias = 'lzz1f';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 2F', 'Lower Zig Zag 2F' FROM parts WHERE part_alias = 'lzz2f';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 2 ban top', 'Lower Zig Zag 2 ban top' FROM parts WHERE part_alias = 'lzz2bt';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 3F', 'Lower Zig Zag 3F' FROM parts WHERE part_alias = 'lzz3f';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 3 ban Upper Base', 'Lower Zig Zag 3 ban Upper Base' FROM parts WHERE part_alias = 'lzz3bub';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 3 ban Upper Base Base', 'Lower Zig Zag 3 ban Upper Base Base' FROM parts WHERE part_alias = 'lzz3bubb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag Net', 'Lower Zig Zag Net' FROM parts WHERE part_alias = 'lzzn';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag', 'Lower Zig Zag' FROM parts WHERE part_alias = 'lzz';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lowest Back Left Small Marble Curve', 'Lowest Back Left Small Marble Curve' FROM parts WHERE part_alias = 'lblsmc';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lowest Largest Backtracking Chopstick', 'Lowest Largest Backtracking Chopstick' FROM parts WHERE part_alias = 'llbc';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lowest Largest Backtracking Popsicle Stick', 'Lowest Largest Backtracking Popsicle Stick' FROM parts WHERE part_alias = 'llbps';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lowest Largest U-Turn', 'Lowest Largest U-Turn' FROM parts WHERE part_alias = 'llut';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Medium Catcher Below Triple Splitter', 'Medium Catcher Below Triple Splitter' FROM parts WHERE part_alias = 'mcbts';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Ninth Placed Outer Spiral Support', 'Ninth Placed Outer Spiral Support' FROM parts WHERE part_alias = '9poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Outer Spiral', 'Outer Spiral' FROM parts WHERE part_alias = 'os';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Right Side Small Landing Zone', 'Right Side Small Landing Zone' FROM parts WHERE part_alias = 'rsslz';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Second Placed Outer Spiral Support', 'Second Placed Outer Spiral Support' FROM parts WHERE part_alias = '2poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Seventh Placed Outer Spiral Support', 'Seventh Placed Outer Spiral Support' FROM parts WHERE part_alias = '7poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Sixth Placed Outer Spiral Support', 'Sixth Placed Outer Spiral Support' FROM parts WHERE part_alias = '6poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Snake Plate Chopstick', 'Snake Plate Chopstick' FROM parts WHERE part_alias = 'spc';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Snake Plate U Turn', 'Snake Plate U Turn' FROM parts WHERE part_alias = 'sput';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Snake Plate U-Turn Berm', 'Snake Plate U-Turn Berm' FROM parts WHERE part_alias = 'sputb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Snake Plate U-turn Berm Bar', 'Snake Plate U-turn Berm Bar' FROM parts WHERE part_alias = 'sputbb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Snake Plate', 'Snake Plate' FROM parts WHERE part_alias = 'sp';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'The First Track', 'The First Track' FROM parts WHERE part_alias = 'tft';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Third Placed Outer Spiral Support', 'Third Placed Outer Spiral Support' FROM parts WHERE part_alias = '3poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder Inner Bar Lower Support', 'Triple Splitter Small Feeder Inner Bar Lower Support' FROM parts WHERE part_alias = 'tssfibls';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder Inner Bar', 'Triple Splitter Small Feeder Inner Bar' FROM parts WHERE part_alias = 'tssfib';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder Outer Bar Lower Support', 'Triple Splitter Small Feeder Outer Bar Lower Support' FROM parts WHERE part_alias = 'tssfobls';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder Outer Bar', 'Triple Splitter Small Feeder Outer Bar' FROM parts WHERE part_alias = 'tssfob';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder', 'Triple Splitter Small Feeder' FROM parts WHERE part_alias = 'tssf';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter', 'Triple Splitter' FROM parts WHERE part_alias = 'ts';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Two Ends Chopped Off Triple Splitter', 'Two Ends Chopped Off Triple Splitter' FROM parts WHERE part_alias = 'tecots';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Upper Splitter off the Lowest Small-Medium Splitter', 'Upper Splitter off the Lowest Small-Medium Splitter' FROM parts WHERE part_alias = 'usotlsms';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'the Lowest Small-Medium Splitter', 'the Lowest Small-Medium Splitter' FROM parts WHERE part_alias = 'tlsms';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'the Triple Splitter Small Marble Catcher', 'the Triple Splitter Small Marble Catcher' FROM parts WHERE part_alias = 'ttssmc';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', '円周螺旋', '円周螺旋' FROM parts WHERE part_alias = 'jos';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lowest Largest Backtrack', 'Lowest Largest Backtrack' FROM parts WHERE part_alias = 'llb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Small Marble Saver', 'Prevents small marbles from falling off the track before Decorative Walls after the Lowest Small-Medium Splitter' FROM parts WHERE part_alias = 'sms';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder Feeder', 'Triple Splitter Small Feeder Feeder' FROM parts WHERE part_alias = 'tssff';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Guider', 'Caret Splitter Feeder Guider' FROM parts WHERE part_alias = 'csfg';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Protection', 'Caret Splitter Feeder Extension Reflection Protection' FROM parts WHERE part_alias = 'csferp';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 3 ban Lower Support', 'Lower Zig Zag 3 ban Lower Support' FROM parts WHERE part_alias = 'lzz3bls';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lowest Largest U-turn Berm', 'Lowest Largest U-turn Berm' FROM parts WHERE part_alias = 'llutb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Protection Holders', 'Caret Splitter Feeder Extension Reflection Protection Holders' FROM parts WHERE part_alias = 'csferph';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Protection Upper Holder', 'Caret Splitter Feeder Extension Reflection Protection Upper Holder' FROM parts WHERE part_alias = 'csferpuh';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Guider Lower Supporter', 'Caret Splitter Feeder Guider Lower Supporter' FROM parts WHERE part_alias = 'csfgls';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Lefter Guider', 'Caret Splitter Feeder Lefter Guider' FROM parts WHERE part_alias = 'csflg';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Righter Guider', 'Caret Splitter Feeder Righter Guider' FROM parts WHERE part_alias = 'csfrg';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Guider Upper Supporter', 'Caret Splitter Feeder Guider Upper Supporter' FROM parts WHERE part_alias = 'csfgus';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Protection Lower Holder', 'Caret Splitter Feeder Extension Reflection Protection Lower Holder' FROM parts WHERE part_alias = 'csferplh';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Zeroth_placed_outer_spiral_support', '000p_zeroth_placed_outer_spiral_support' FROM parts WHERE part_alias = '0poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Tenth Placed Outer Spiral Support', 'Tenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '10poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'el Lifty Lever', 'el Lifty Lever' FROM parts WHERE part_alias = 'eLL';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Rudder Lever Spinner', 'Caret Splitter Rudder Lever Spinner' FROM parts WHERE part_alias = 'csrls';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Below Lower Zig Zag U-turn', 'Below Lower Zig Zag U-turn' FROM parts WHERE part_alias = 'blzzut';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'The Grand Archway', 'The Grand Archway' FROM parts WHERE part_alias = 'tga';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Lever', 'Caret Splitter Lever' FROM parts WHERE part_alias = 'csl';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Eleventh placed Outer Spiral Support', 'Eleventh placed Outer Spiral Support' FROM parts WHERE part_alias = '11poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'el Lifty Lever Guider', 'el Lifty Lever Guider' FROM parts WHERE part_alias = 'ellg';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 3F 2F Net', 'Lower Zig Zag 3F 2F Net' FROM parts WHERE part_alias = 'lzz32n';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag Triple Splitter Small Feeder Separator', 'Lower Zig Zag Triple Splitter Small Feeder Separator' FROM parts WHERE part_alias = 'lzztssfs';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Twelfth placed Outer Spiral Support', 'Twelfth placed Outer Spiral Support' FROM parts WHERE part_alias = '12poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 2F Bouncer', 'Lower Zig Zag 2F Bouncer' FROM parts WHERE part_alias = 'lzz2fb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Medium Catcher Ridge', 'Triple Splitter Medium Catcher Ridge' FROM parts WHERE part_alias = 'tsmcr';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'The Lowest Longest Separator', 'The Lowest Longest Separator' FROM parts WHERE part_alias = 'tlls';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Lower Right Protection', 'Caret Splitter Feeder Extension Reflection Lower Right Protection' FROM parts WHERE part_alias = 'csferlrp';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Feeder Extension Reflection Lower Left Protection', 'Caret Splitter Feeder Extension Reflection Lower Left Protection' FROM parts WHERE part_alias = 'csferllp';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Second Circle Below Caret Splitter', 'Second Circle Below Caret Splitter' FROM parts WHERE part_alias = 'scbcs';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Small Medium Splitter Inner Thinner Keeper', 'Lower Small Medium Splitter Inner Thinner Keeper' FROM parts WHERE part_alias = 'lsmsitk';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Second Bridge Supports', 'Second Bridge Supports' FROM parts WHERE part_alias = '2bs';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Second Bridge Level 1', 'Second Bridge Level 1' FROM parts WHERE part_alias = '2bl1';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', '2nd bridge bridge level 1', '2nd bridge bridge level 1' FROM parts WHERE part_alias = '2bbl1';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Thirteenth Placed Outer Spiral Support', 'Thirteenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '13poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'El Banderín', 'El Banderín' FROM parts WHERE part_alias = 'eb';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 2F Guardrails', 'Lower Zig Zag 2F Guardrails' FROM parts WHERE part_alias = 'lzz2fg';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Fourteenth Placed Outer Spiral Support', 'Fourteenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '14poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Left Side Arch', 'Lower Left Side Arch' FROM parts WHERE part_alias = 'llsa';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 1F Inner Rails', 'Lower Zig Zag 1F Inner Rails' FROM parts WHERE part_alias = 'lzz1fir';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Fifteenth Placed Outer Spiral Support', 'Fifteenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '15poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Right Hand Side Inner Guider', 'Caret Splitter Right Hand Side Inner Guider' FROM parts WHERE part_alias = 'csrhsig';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Caret Splitter Right Hand Side Outer Guider', 'Caret Splitter Right Hand Side Outer Guider' FROM parts WHERE part_alias = 'csrhsog';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 3F Net Affector', 'Lower Zig Zag 3F Net Affector' FROM parts WHERE part_alias = 'lzz3fna';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'The Third Arch', 'The Third Arch' FROM parts WHERE part_alias = '3rda';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'DC 48', 'DC 48' FROM parts WHERE part_alias = 'dc48';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Paul', 'Paul' FROM parts WHERE part_alias = 'paul';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Zog', 'Zog' FROM parts WHERE part_alias = 'zog';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Sixteenth Placed Outer Spiral Support', 'Sixteenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '16poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Seventeenth Placed Outer Spiral Support', 'Seventeenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '17poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Lower Zig Zag 3F Rails', 'Lower Zig Zag 3F Rails' FROM parts WHERE part_alias = 'lzz3fr';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Zog Rails Support', 'Zog Rails Support' FROM parts WHERE part_alias = 'zogrs';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Zog Rails', 'Zog Rails' FROM parts WHERE part_alias = 'zogr';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder Inner Guider', 'Triple Splitter Small Feeder Inner Guider' FROM parts WHERE part_alias = 'tssfig';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Triple Splitter Small Feeder Outer Guider', 'Triple Splitter Small Feeder Outer Guider' FROM parts WHERE part_alias = 'tssfog';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Eighteenth Placed Outer Spiral Support', 'Eighteenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '18poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Nineteenth Placed Outer Spiral Support', 'Nineteenth Placed Outer Spiral Support' FROM parts WHERE part_alias = '19poss';
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', 'Twentieth Placed Outer Spiral Support', 'Twentieth Placed Outer Spiral Support' FROM parts WHERE part_alias = '20poss';
