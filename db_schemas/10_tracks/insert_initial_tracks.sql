-- Insert the five landing zones first
INSERT INTO tracks (track_alias, track_name, track_description, is_transport, is_splitter, is_landing_zone, marble_sizes_accepted) VALUES
('tft', 'The First Track', 'Terminal destination that receives all large marbles', FALSE, FALSE, TRUE, 'large'),
('lsslz', 'Left Side Small Landing Zone', 'Terminal destination for small marbles on the left side', FALSE, FALSE, TRUE, 'small'),
('rsslz', 'Right Side Small Landing Zone', 'Terminal destination for small marbles on the right side', FALSE, FALSE, TRUE, 'small'),
('brmlz', 'Back Right Medium Landing Zone', 'Terminal destination for medium marbles in the back right', FALSE, FALSE, TRUE, 'medium'),
('flmlz', 'Front Left Medium Landing Zone', 'Terminal destination for medium marbles in the front left', FALSE, FALSE, TRUE, 'medium');

-- Insert major track systems
INSERT INTO tracks (track_alias, track_name, track_description, is_transport, is_splitter, is_landing_zone, marble_sizes_accepted) VALUES
('outer_spiral', 'Outer Spiral', 'Transports medium and large marbles around the perimeter of the stage', TRUE, FALSE, FALSE, 'medium,large'),
('triple_splitter_system', 'Triple Splitter System', 'Transports marbles and splits them by size into different paths', TRUE, TRUE, FALSE, 'small,medium,large');

-- Create track-to-track connections showing marble flow
INSERT INTO track_connections (from_track_id, to_track_id, marble_sizes, connection_type, connection_description) VALUES
-- Outer Spiral feeds into Triple Splitter System
((SELECT track_id FROM tracks WHERE track_alias = 'outer_spiral'),
 (SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 'medium,large', 'direct', 'Outer Spiral feeds medium and large marbles into Triple Splitter'),

-- Triple Splitter System splits to landing zones
((SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 (SELECT track_id FROM tracks WHERE track_alias = 'tft'),
 'large', 'split', 'Large marbles go to The First Track'),

((SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 (SELECT track_id FROM tracks WHERE track_alias = 'lsslz'),
 'small', 'split', 'Some small marbles go to Left Side Small Landing Zone'),

((SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 (SELECT track_id FROM tracks WHERE track_alias = 'rsslz'),
 'small', 'split', 'Some small marbles go to Right Side Small Landing Zone'),

((SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 (SELECT track_id FROM tracks WHERE track_alias = 'brmlz'),
 'medium', 'split', 'Some medium marbles go to Back Right Medium Landing Zone'),

((SELECT track_id FROM tracks WHERE track_alias = 'triple_splitter_system'),
 (SELECT track_id FROM tracks WHERE track_alias = 'flmlz'),
 'medium', 'split', 'Some medium marbles go to Front Left Medium Landing Zone');
