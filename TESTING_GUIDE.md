# Testing Guide for Marble Track 3

This guide explains how to use the testing infrastructure added to catch bugs like those found in issues #57 and #58.

## Overview

The testing system provides both unit tests and integration tests to validate form field mappings, SQL parameter counts, and database operations. This would have caught the duplicate name attribute bug from issue #57.

## Quick Start

### 1. Basic Validation Tests (No Database Required)

Run the simple validation tests to check form field mappings and SQL parameter validation:

```bash
php scripts/run_simple_tests.php
```

These tests verify:
- ✅ Parts form has correct `name="part_description"` attribute
- ✅ Workers form has correct `name="description"` attribute  
- ✅ No duplicate `name="notes"` attributes (the bug from #57)
- ✅ SQL parameter counts match placeholder counts

### 2. Full PHPUnit Tests (Requires Composer)

If you have composer installed:

```bash
# Install dependencies
php composer.phar install

# Run all tests
php composer.phar run test

# Run only unit tests
php composer.phar run test-unit

# Run only integration tests  
php composer.phar run test-integration
```

### 3. Database Integration Tests (Requires Test Database)

For full integration testing, you need a test database set up:

```bash
# Check test database access
php scripts/setup_test_database.php check

# Sync test database with production data
php scripts/setup_test_database.php sync

# Full setup (check + sync + validate)
php scripts/setup_test_database.php setup
```

## Test Database Setup (Dreamhost Shared Hosting)

Since we're on Dreamhost shared hosting, test database setup requires manual steps:

### One-Time Setup

1. **Create Test Database via Dreamhost Panel:**
   - Log into Dreamhost panel
   - Go to "MySQL Databases"  
   - Create new database: `marbletrack3_test`
   - Create new user or grant access to existing user

2. **Update Configuration:**
   - Copy `classes/ConfigSample.php` to `classes/Config.php`
   - Set test database credentials in `Config.php`
   - Or create a separate `TestConfig` class

3. **Initial Sync:**
   ```bash
   php scripts/setup_test_database.php setup
   ```

### Regular Maintenance

Sync test database with production periodically:

```bash
# Weekly sync recommended
php scripts/setup_test_database.php sync
```

## What These Tests Catch

### 1. Form Field Mapping Issues (Issue #57)

The tests validate that HTML form field names match what PHP handlers expect:

```php
// ❌ This would be caught by tests:
<textarea name="notes" name="part_description">

// ✅ This is correct:
<textarea name="part_description">
```

### 2. SQL Parameter Mismatches

Tests validate that SQL placeholders match parameter counts:

```php
// ❌ This would be caught:
$sql = "UPDATE moments SET notes = ?, frame_start = ? WHERE id = ?";
$params = ['note', 100]; // Missing 3rd parameter!

// ✅ This is correct:
$sql = "UPDATE moments SET notes = ?, frame_start = ? WHERE id = ?";  
$params = ['note', 100, 1]; // All parameters provided
```

### 3. Database Operations

Integration tests verify:
- Parts can be created and updated correctly
- Form data flows properly to database
- SQL queries execute without errors
- Data is saved with correct field mappings

## Test Structure

```
tests/
├── bootstrap.php           # Test setup and configuration
├── Unit/
│   └── FormFieldMappingTest.php  # Form validation tests
└── Integration/
    ├── PartsIntegrationTest.php   # Parts database tests
    └── WorkersIntegrationTest.php # Workers database tests

scripts/
├── setup_test_database.php    # Database sync utility
└── run_simple_tests.php      # Quick validation tests
```

## Files Added

- `composer.json` - PHP dependencies for testing
- `phpunit.xml` - PHPUnit configuration
- `tests/bootstrap.php` - Test environment setup
- `tests/Unit/FormFieldMappingTest.php` - Form field validation tests
- `tests/Integration/PartsIntegrationTest.php` - Parts database tests  
- `tests/Integration/WorkersIntegrationTest.php` - Workers database tests
- `scripts/setup_test_database.php` - Test database management
- `scripts/run_simple_tests.php` - Quick validation without PHPUnit
- `classes/Config.php` - Basic configuration (you need to customize this)

## Running Tests in CI/CD

For external CI/CD (GitHub Actions), see the examples in `TESTING.md`. The tests can run with external MySQL databases since Dreamhost shared hosting limits automated database creation.

## Adding New Tests

### Unit Tests

Add new unit tests to `tests/Unit/` for:
- Form validation
- Business logic
- SQL query validation

### Integration Tests  

Add new integration tests to `tests/Integration/` for:
- Database operations
- Complete user workflows
- API endpoints

### Example Test

```php
public function testNewFeatureFormMapping()
{
    $templatePath = __DIR__ . '/../../templates/admin/new_feature.tpl.php';
    $templateContent = file_get_contents($templatePath);
    
    // Validate form fields match handler expectations
    $this->assertStringContainsString('name="expected_field"', $templateContent);
    $this->assertStringNotContainsString('name="duplicate_field"', $templateContent);
}
```

## Benefits

This testing infrastructure provides:

1. **Regression Prevention** - Catches bugs like #57 before they reach production
2. **Documentation** - Tests serve as examples of correct usage
3. **Confidence** - Safe refactoring with automated validation
4. **Compatibility** - Works within Dreamhost shared hosting constraints
5. **Gradual Adoption** - Can add tests incrementally as needed

The key insight is that while shared hosting limits our automation options, we can still achieve effective testing through manual setup and leveraging existing backup systems for data sync.