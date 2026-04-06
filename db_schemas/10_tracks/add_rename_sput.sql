-- Rename snake_plate_u_turn track alias to match part alias sput
UPDATE tracks SET track_alias = 'sput' WHERE track_alias = 'snake_plate_u_turn';
