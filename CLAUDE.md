# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **Marble Track 3**, a long-term stop motion animation project database and website. The project tracks Workers (pipe cleaner figures) building a physical marble track, organizing thousands of frames into snippets for compilation into a full-length movie.

## Development Commands

### Database Management
```bash
# Dreamhost shared hosting - no root access
# No viable testing framework available
# Database operations use DBPersistaroo for backups
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
- No viable testing framework available  
- Cannot populate test databases using DBPersistaroo
- Manual database operations via Dreamhost panel only
- Focus on careful development and manual validation

## Development Notes

- Uses custom autoloader (`classes/Mlaphp/Autoloader.php`)
- Designed for Dreamhost shared hosting environment
- No external frameworks - pure PHP with custom abstractions
- Database schemas in `db_schemas/` organized by feature area
- Manual validation required due to hosting constraints
- `DBPersistaroo` handles automatic database backups (hourly)