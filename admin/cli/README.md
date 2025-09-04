# Media Harmony WP CLI Commands

This plugin provides WordPress CLI commands for managing media files and performing media cleanup operations.

## Prerequisites

- WordPress CLI must be installed and accessible
- Advanced Custom Fields PRO plugin must be activated
- Plugin must have valid authentication

## Available Commands

### Main Commands

```bash
wp media-harmony <subcommand> [options]
```

### Media Commands

```bash
wp media-harmony media <subcommand> [options]
```

## Subcommands

### 1. Media Scan

Scans for unused media files in your WordPress installation.

```bash
wp media-harmony media scan
```

**Description:** This command analyzes your WordPress database and file system to identify media files that are not being used in posts, pages, or custom fields.

### 2. Media Clean

Removes unused media files from your WordPress installation.

```bash
wp media-harmony media clean [options]
```

**Options:**
- `--dry-run`: Preview what would be deleted without actually deleting files
- `--force`: Skip confirmation prompt

**Examples:**
```bash
# Preview what would be deleted
wp ronik media clean --dry-run

# Force delete without confirmation
wp ronik media clean --force

# Interactive delete with confirmation
wp ronik media clean
```

**⚠️ Warning:** This command permanently deletes files. Always use `--dry-run` first to preview changes.

### 3. Media Statistics

Displays statistics about your media library.

```bash
wp media-harmony media stats
```

**Output includes:**
- Total number of attachments
- Number of used attachments
- Number of unused attachments
- Total file size
- Recommendations for cleanup

### 4. Media Preserve

Marks a specific media file as preserved (prevents deletion).

```bash
wp media-harmony media preserve <media_id>
```

**Example:**
```bash
wp ronik media preserve 123
```

### 5. Media Unpreserve

Removes preservation status from a media file.

```bash
wp media-harmony media unpreserve <media_id>
```

**Example:**
```bash
wp ronik media unpreserve 123
```

## Shortcut Commands

You can also use these direct commands:

```bash
wp media-harmony scan          # Same as: wp media-harmony media scan
wp media-harmony clean         # Same as: wp media-harmony media clean
wp media-harmony stats         # Same as: wp media-harmony media stats
```

## Examples

### Complete Media Cleanup Workflow

```bash
# 1. Check current status
wp media-harmony media stats

# 2. Scan for unused files
wp media-harmony media scan

# 3. Preview what would be deleted
wp media-harmony media clean --dry-run

# 4. Perform actual cleanup
wp media-harmony media clean

# 5. Verify results
wp media-harmony media stats
```

### Batch Operations

```bash
# Preserve multiple files
wp media-harmony media preserve 123
wp media-harmony media preserve 456
wp media-harmony media preserve 789

# Check preservation status
wp media-harmony media stats
```

## Error Handling

The CLI commands include comprehensive error handling:

- **Dependency checks**: Verifies ACF PRO is active
- **Authentication checks**: Ensures plugin has valid license
- **File system checks**: Validates file existence before operations
- **Database integrity**: Uses WordPress core functions for safe deletion

## Safety Features

- **Dry-run mode**: Preview changes before execution
- **Confirmation prompts**: Interactive confirmation for destructive operations
- **File validation**: Checks file existence and permissions
- **Rollback support**: Uses WordPress core deletion functions

## Troubleshooting

### Common Issues

1. **"ACF PRO not active" error**
   - Ensure Advanced Custom Fields PRO is installed and activated

2. **"Authentication required" error**
   - Verify plugin license is valid
   - Check plugin settings

3. **"Permission denied" error**
   - Ensure WordPress CLI has proper file system permissions
   - Check WordPress file ownership

### Debug Mode

Enable WordPress debug mode for detailed error information:

```bash
# In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Support

For issues or questions:
- Check the plugin documentation
- Review WordPress CLI logs
- Contact plugin support

## Version History

- **1.0.0**: Initial CLI implementation
  - Basic media scan and cleanup
  - Statistics and preservation features
  - Safety features and error handling
