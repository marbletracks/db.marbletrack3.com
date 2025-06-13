CREATE TABLE workers (
  worker_id INT AUTO_INCREMENT PRIMARY KEY,
  worker_alias VARCHAR(10) NOT NULL UNIQUE
) COMMENT='Each worker is a character in Marble Track 3. Alias is an ASCII-safe unique code.';

INSERT INTO workers (worker_alias) VALUES
  ('square'),
  ('rab'),
  ('lil'),
  ('cm'),
  ('rg'),
  ('big'),
  ('ds'),
  ('mmg'),
  ('gar'),
  ('francois'),
  ('gc'),
  ('mrg'),
  ('pink'),
  ('sup'),
  ('bpj'),
  ('auto');


CREATE TABLE worker_names (
  worker_name_id INT AUTO_INCREMENT PRIMARY KEY,
  worker_id INT NOT NULL,
  language_code CHAR(2) NOT NULL,
  worker_name VARCHAR(255) NOT NULL,
  worker_description TEXT,
  FOREIGN KEY (worker_id) REFERENCES workers(worker_id)
    ON DELETE CASCADE
);

INSERT INTO worker_names (worker_id, language_code, worker_name, worker_description) VALUES
  ((SELECT worker_id FROM workers WHERE worker_alias = 'square'), 'US',
    'Squarehead', 'Squarehead often gets confused, often seen clumsily and not knowing what''s going on.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'rab'), 'US',
    'Rabby', 'Rabby had a cameo appearance to bring the lower base into place.  He knocked the camera off its stand when he walked off set.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'lil'), 'US',
    'Little Brother', 'Little Brother, focused on his own world, usually plays around on the track by spinning or gymnastically flipping around.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'cm'), 'US',
    'Candy Mama', 'Candy Mama is the feminine powerhouse behind lots of the action; she knows what''s going on and what each worker is up to.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'rg'), 'US',
    'Reversible Guy', 'Reversible Guy''s head looks the same coming.  He can reverse his direction of travel in an instant and likes to do this when collecting things from off the set.  He can walk through tracks as needed.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'big'), 'US',
    'Big Brother', 'Big Brother, sometimes surly, does actually love his little brother.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'ds'), 'US',
    'Doctor Sugar', 'Doctor Sugar is the project manager; he knows everything that''s going on from a technical perspective'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'mmg'), 'US',
    'Mr McGlue', 'Mr McGlue has one long green arm which delivers glue to parts that need to be connected.  It takes one second to glue each piece in place.  He cannot fly, but he can walk through tracks.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'gar'), 'US',
    'Garinoppi', 'Garinoppi is a skeleton who had a cameo appearance flying the stage rotation bearing into place.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'francois'), 'US',
    'François', 'François had a cameo appearance to bring the upper base into place. He deftly kicked it to be exactly centered so it could rotate properly.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'gc'), 'US',
    'G Choppy', 'G Choppy is an expert wood cutter, carver, and wood bender.  He cannot walk through tracks, but he can fly as needed.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'mrg'), 'US',
    'Mr Greene', 'Mr Greene basically knows everything Dr Sugar knows.  He''s a diligent hard worker.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'pink'), 'US',
    'Pinky', 'Pinky has an eye for design and is a relatively diligent worker.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'sup'), 'US',
    'Super Spoony', 'Super Spoony expertly moves marbles on his head, or are the marbles his head?'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'bpj'), 'US',
    'Backpack Jack', 'Backpack Jack has a backpack he sometimes uses to carry things, but he cannot reach into it to take them out.'),
  ((SELECT worker_id FROM workers WHERE worker_alias = 'auto'), 'US',
    'Autosticks', 'Autosticks are magically animated toothpicks who slide their way onto the set where they need to go.');
