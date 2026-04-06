-- Rides: named end-to-end marble journeys through multiple tracks
-- A Ride is the visitor-facing experience; Tracks are segments along the way.
CREATE TABLE rides (
  ride_id INT AUTO_INCREMENT PRIMARY KEY,
  ride_alias VARCHAR(50) NOT NULL UNIQUE,
  ride_name VARCHAR(255) NOT NULL,
  ride_description TEXT COMMENT 'Visitor-facing description of the experience',
  ride_tagline VARCHAR(255) COMMENT 'Short teaser, e.g. "The longest ride on the track"',
  marble_size ENUM('small', 'medium', 'large') NOT NULL,
  is_complete BOOLEAN DEFAULT FALSE COMMENT 'All tracks in this ride are built and functional',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT='Named marble journeys. Each ride visits a sequence of tracks.';

-- Ordered sequence of tracks within a ride
CREATE TABLE ride_tracks (
  ride_track_id INT AUTO_INCREMENT PRIMARY KEY,
  ride_id INT NOT NULL,
  track_id INT NOT NULL,
  sequence_order INT NOT NULL COMMENT 'Order in which this track is visited (1, 2, 3...)',
  experience_note TEXT COMMENT 'What the marble experiences here, e.g. "full length", "hairpin turn"',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ride_id) REFERENCES rides(ride_id) ON DELETE CASCADE,
  FOREIGN KEY (track_id) REFERENCES tracks(track_id) ON DELETE CASCADE,
  UNIQUE KEY unique_ride_track_order (ride_id, sequence_order)
) COMMENT='Maps which tracks a ride visits and in what order.';

-- Photos for rides (hero images, route maps)
CREATE TABLE rides_2_photos (
  ride_id INT NOT NULL,
  photo_id INT NOT NULL,
  photo_sort INT NOT NULL DEFAULT 0,
  is_primary BOOLEAN DEFAULT NULL,
  PRIMARY KEY (ride_id, photo_id),
  FOREIGN KEY (ride_id) REFERENCES rides(ride_id) ON DELETE CASCADE,
  FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
) COMMENT='Photos associated with rides.';
