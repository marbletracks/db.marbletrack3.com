-- Initialize test database for Marble Track 3 with full schema
-- This loads the complete production schema for testing

-- Load the complete schema from dbmt3.sql
SOURCE /docker-entrypoint-initdb.d/dbmt3.sql;

-- Rename database from dbmt3 to dbmt3_test for testing
CREATE DATABASE IF NOT EXISTS dbmt3_test;

-- Copy all tables to test database
CREATE TABLE dbmt3_test.applied_DB_versions LIKE dbmt3.applied_DB_versions;
CREATE TABLE dbmt3_test.columns LIKE dbmt3.columns;
CREATE TABLE dbmt3_test.cookies LIKE dbmt3.cookies;
CREATE TABLE dbmt3_test.episodes LIKE dbmt3.episodes;
CREATE TABLE dbmt3_test.episodes_2_photos LIKE dbmt3.episodes_2_photos;
CREATE TABLE dbmt3_test.livestreams LIKE dbmt3.livestreams;
CREATE TABLE dbmt3_test.livestream_transcripts LIKE dbmt3.livestream_transcripts;
CREATE TABLE dbmt3_test.moments LIKE dbmt3.moments;
CREATE TABLE dbmt3_test.moments_2_photos LIKE dbmt3.moments_2_photos;
CREATE TABLE dbmt3_test.moment_translations LIKE dbmt3.moment_translations;
CREATE TABLE dbmt3_test.notebooks LIKE dbmt3.notebooks;
CREATE TABLE dbmt3_test.notebooks_2_photos LIKE dbmt3.notebooks_2_photos;
CREATE TABLE dbmt3_test.pages LIKE dbmt3.pages;
CREATE TABLE dbmt3_test.pages_2_photos LIKE dbmt3.pages_2_photos;
CREATE TABLE dbmt3_test.parts LIKE dbmt3.parts;
CREATE TABLE dbmt3_test.parts_2_photos LIKE dbmt3.parts_2_photos;
CREATE TABLE dbmt3_test.parts_oss_status LIKE dbmt3.parts_oss_status;
CREATE TABLE dbmt3_test.part_connections LIKE dbmt3.part_connections;
CREATE TABLE dbmt3_test.part_histories LIKE dbmt3.part_histories;
CREATE TABLE dbmt3_test.part_history_photos LIKE dbmt3.part_history_photos;
CREATE TABLE dbmt3_test.part_history_translations LIKE dbmt3.part_history_translations;
CREATE TABLE dbmt3_test.part_translations LIKE dbmt3.part_translations;
CREATE TABLE dbmt3_test.photos LIKE dbmt3.photos;
CREATE TABLE dbmt3_test.phrases LIKE dbmt3.phrases;
CREATE TABLE dbmt3_test.shortcodes LIKE dbmt3.shortcodes;
CREATE TABLE dbmt3_test.takes LIKE dbmt3.takes;
CREATE TABLE dbmt3_test.tokens LIKE dbmt3.tokens;
CREATE TABLE dbmt3_test.users LIKE dbmt3.users;
CREATE TABLE dbmt3_test.workers LIKE dbmt3.workers;
CREATE TABLE dbmt3_test.workers_2_photos LIKE dbmt3.workers_2_photos;
CREATE TABLE dbmt3_test.worker_names LIKE dbmt3.worker_names;

-- Basic test data to verify connection
CREATE TABLE IF NOT EXISTS dbmt3_test.test_connection (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO dbmt3_test.test_connection (message) VALUES ('Docker MySQL is working with full schema!');