-- Main tracks table for logical groupings of parts
CREATE TABLE tracks (
  track_id INT AUTO_INCREMENT PRIMARY KEY,
  track_alias VARCHAR(50) NOT NULL UNIQUE,
  track_name VARCHAR(255) NOT NULL,
  track_description TEXT,
  is_transport BOOLEAN DEFAULT FALSE,
  is_splitter BOOLEAN DEFAULT FALSE,
  is_landing_zone BOOLEAN DEFAULT FALSE,
  marble_sizes_accepted SET('small', 'medium', 'large') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT='Logical tracks made up of multiple parts. Tracks transport marbles via gravity.';

-- Track-to-track flow connections
CREATE TABLE track_connections (
  track_connection_id INT AUTO_INCREMENT PRIMARY KEY,
  from_track_id INT NOT NULL,
  to_track_id INT NOT NULL,
  marble_sizes SET('small', 'medium', 'large') NOT NULL,
  connection_type ENUM('direct', 'split', 'merge') NOT NULL DEFAULT 'direct',
  connection_description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (from_track_id) REFERENCES tracks(track_id) ON DELETE CASCADE,
  FOREIGN KEY (to_track_id) REFERENCES tracks(track_id) ON DELETE CASCADE,
  UNIQUE KEY unique_track_connection (from_track_id, to_track_id, connection_type)
) COMMENT='Marble flow connections between tracks';

-- Many-to-many relationship between tracks and parts
CREATE TABLE track_parts (
  track_part_id INT AUTO_INCREMENT PRIMARY KEY,
  track_id INT NOT NULL,
  part_id INT NOT NULL,
  part_role ENUM('main', 'support', 'connector', 'guide') NOT NULL DEFAULT 'main',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (track_id) REFERENCES tracks(track_id) ON DELETE CASCADE,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE,
  UNIQUE KEY unique_track_part (track_id, part_id)
) COMMENT='Maps which parts belong to which tracks';