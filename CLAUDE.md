# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **Marble Track 3**, a long-term stop motion animation project database and website. The project tracks Workers (pipe cleaner figures) building a physical marble track, organizing thousands of frames into snippets for compilation into a full-length movie.

## Development Commands

### Database Management
```bash
# Dreamhost shared hosting - no root access
# Test database sync and validation now working
# Database operations use DBPersistaroo for backups

# Sync test database with production data
php scripts/setup_test_database.php sync

# Validate test database is working
php scripts/setup_test_database.php validate

# Run tests (no PHPUnit required)
php scripts/run_simple_tests.php
```

## Architecture

### Core Domain Classes

**Physical Objects** (`classes/Physical/`):
- `Worker.php` - Pipe cleaner figures that build the track
- `Part.php` - Physical components of the marble track
- `Notebook.php`, `Page.php`, `Column.php` - Documentation system

**Media Objects** (`classes/Media/`):
- `Frame.php` - Individual animation frames
- `Take.php` - Groups of frames from a single recording session
- `Moment.php` - Significant events during construction
- `Episode.php` - Larger segments of the animation

**Repository Pattern** (`classes/Database/`):
- All data access goes through Repository classes
- `MomentRepository.php`, `PartsRepository.php`, `WorkersRepository.php`, etc.
- Database abstraction through `Database.php` and `DbInterface.php`

### Template System

PHP template system in `templates/`:
- `admin/` - Administrative interface templates
- `frontend/` - Public-facing templates  
- `layout/` - Base layout templates
- Templates use `.tpl.php` extension

### Request Routing

Entry points in `wwwroot/`:
- `index.php` - Frontend router
- `admin/index.php` - Admin interface router
- URL structure: `/admin/[entity]/[action].php`

## Key Patterns

### Form Field Mapping
**Critical**: Form field `name` attributes must exactly match repository method parameters. Be careful of duplicate `name` attributes (Issue #57).

### Database Operations
- All SQL queries use prepared statements  
- Parameter counts must match placeholder counts
- Use `DBPersistaroo` for automatic database backups

### Error Handling
Use `EDatabaseExceptions.php` for database-specific errors.

## Hosting Environment

**Dreamhost Shared Hosting Constraints:**
- No root access or shell access
- PHPUnit not available, but custom test runner works
- Test database must be created manually via Dreamhost panel
- DBPersistaroo syncs production data to test database automatically
- Focus on integration testing with real database

## Testing

### Test Database Setup
Test database `dbmt3_test` must be created manually via Dreamhost panel with same credentials as production.

### Available Testing Tools
- `scripts/setup_test_database.php` - Syncs production data to test database using DBPersistaroo backups
- `scripts/run_simple_tests.php` - Custom test runner (no PHPUnit dependency)
- `tests/bootstrap.php` - Test environment bootstrap with TestConfig class

### Critical Bug Prevention
Tests validate:
- Form field `name` attributes match repository parameters (Issue #57)
- SQL parameter counts match placeholder counts
- Database schema consistency between production and test

### Test Workflow
1. `php scripts/setup_test_database.php sync` - Refresh test data
2. `php scripts/run_simple_tests.php` - Run validation tests
3. Manual testing against test database for complex scenarios

## Development Notes

- Uses custom autoloader (`classes/Mlaphp/Autoloader.php`)
- Designed for Dreamhost shared hosting environment
- No external frameworks - pure PHP with custom abstractions
- Database schemas in `db_schemas/` organized by feature area
- Test-driven development possible with custom test runner
- `DBPersistaroo` handles automatic database backups (hourly)