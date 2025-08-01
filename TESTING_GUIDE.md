# Testing Guide for Marble Track 3

This guide explains how to use the testing infrastructure added to catch bugs like those found in issues #57 and #58.

## Overview

The testing system provides both unit tests and integration tests to validate form field mappings, SQL parameter counts, and database operations. This would have caught the duplicate name attribute bug from issue #57.

## Quick Start

### 1. Comprehensive Test Suite (Recommended)

Run the full test suite covering all validation categories:

```bash
php scripts/run_all_tests.php
```

This runs 5 test categories:
- ✅ Form Field Validation (Issue #57/58 prevention)
- ✅ SQL Parameter Validation
- ✅ AJAX Endpoint Security  
- ✅ Database Connectivity
- ✅ Regression prevention tests

### 2. Legacy Simple Tests (No Database Required)

Run basic validation tests for form field mappings and SQL parameter validation:

```bash
php scripts/run_simple_tests.php
```

These tests verify:
- ✅ Parts form has correct `name="part_description"` attribute
- ✅ Workers form has correct `name="description"` attribute
- ✅ No duplicate `name="notes"` attributes (the bug from #57)
- ✅ SQL parameter counts match placeholder counts

### 3. Full PHPUnit Tests (Requires Composer)

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

**Note**: If you encounter autoloader conflicts with PHPUnit (like `"You call that a file? Unit.php not found"`), this has been fixed in the `classes/Mlaphp/Autoloader.php` to prevent conflicts with PHPUnit's test suite names and classes.

### 4. Database Integration Tests (Requires Test Database)

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

### 3. AJAX Endpoint Security

Tests validate:
- Input validation using proper `filter_var()` functions
- No direct `$_POST` array access warnings
- Consistent error/success response formatting
- RobRequest class usage eliminates security warnings

### 4. Database Operations

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

1. **Regression Prevention** - Catches bugs like #57/58 before they reach production
2. **Documentation** - Tests serve as examples of correct usage
3. **Confidence** - Safe refactoring with automated validation
4. **Compatibility** - Works within Dreamhost shared hosting constraints
5. **Gradual Adoption** - Can add tests incrementally as needed
6. **Security Validation** - AJAX endpoints use proper input validation via RobRequest class

## Code Quality TODOs

### PHP Linting Integration
**Status**: Ready to implement (can install directly on Dreamhost via SSH)

**Installation (Works on Dreamhost via SSH)**:
```bash
# Install PHP_CodeSniffer globally for linting
composer global require squizlabs/php_codesniffer

# Add composer global bin to PATH (add to ~/.bashrc)
export PATH="$HOME/.composer/vendor/bin:$PATH"

# Run linting on codebase
phpcs --standard=PSR12 classes/
phpcs --standard=PSR12 wwwroot/admin/ajax/

# Fix automatically fixable issues
phpcbf --standard=PSR12 classes/
```

**Additional tools available on Dreamhost**:
- **PHPStan** for static analysis: `composer global require phpstan/phpstan`
- **Psalm** for advanced static analysis: `composer global require vimeo/psalm`
- **PHP-CS-Fixer** for automatic formatting: `composer global require friendsofphp/php-cs-fixer`

**Dreamhost Shared Hosting Capabilities**:
- ✅ **Shell access available** via SSH
- ✅ Can install composer packages in user directory
- ✅ Can run command-line tools like `phpcs`
- ❌ **No root access** for system-wide installations
- ❌ Cannot install system packages or modify global PHP configuration

**Integration with existing test runner**:
```bash
# Could add linting to the comprehensive test suite
php scripts/run_all_tests.php --include-lint

# Or create dedicated linting script
php scripts/run_linting.php
```

Since you have SSH access, you can install and run these tools directly on the server, making linting part of your regular development workflow on Dreamhost.

The key insight is that while shared hosting limits our automation options, we can still achieve effective testing through manual setup and leveraging existing backup systems for data sync.

## Troubleshooting

### Common Issues and Solutions

#### 1. PHPUnit Autoloader Conflicts

**Error**: `"You call that a file? (/path/to/classes/Unit.php not found)"`

**Solution**: This error occurs when the existing autoloader conflicts with PHPUnit's test suite naming. This has been fixed in `classes/Mlaphp/Autoloader.php` to prevent conflicts with PHPUnit classes and test suite names.

#### 2. Missing Config.php

**Error**: `"Class Config not found"`

**Solution**: The application expects a `classes/Config.php` file. On production, this is created from `classes/ConfigSample.php`. For testing, the bootstrap will automatically create a temporary one if needed.

```bash
# Manually create Config.php if needed
cp classes/ConfigSample.php classes/Config.php
```

#### 3. Database Connection Issues in Tests

**Error**: `"Database connection failed in tests"`

**Solution**:
1. Make sure you have a test database set up
2. Run `php scripts/setup_test_database.php check` to verify access
3. For unit tests, use the simple scripts that don't require database access

#### 4. Composer Installation Issues on Dreamhost

**Issue**: Composer downloads may time out on shared hosting

**Solution**: Use the simple test scripts that don't require composer:
```bash
php scripts/run_simple_tests.php           # Basic validation
php scripts/test_issues_57_58.php          # Specific bug tests
```

#### 5. Permission Issues

**Error**: `"Permission denied"` when running scripts

**Solution**:
```bash
chmod +x scripts/*.php
```

### Getting Help

If tests are failing and you're not sure why:

1. Run the simple tests first: `php scripts/run_simple_tests.php`
2. Check that your environment matches the working configuration
3. Review the test output for specific assertion failures
4. Ensure you're testing against the correct branch with the fixes applied

### Minimal Testing Setup

If you just need to verify the fixes for issues #57 and #58 work:

```bash
# These require no dependencies and no database
php scripts/test_issues_57_58.php
php scripts/demonstrate_bug_prevention.php
```