# Push Notification Commands

This directory contains commands for managing push notifications in the Botble CMS API package.

## Available Commands

### 1. Send Push Notification Command

**Command:** `cms:push-notification:send`

Send push notifications to mobile apps with various targeting options.

#### Usage Examples

**Interactive Mode:**
```bash
php artisan cms:push-notification:send --interactive
```

**Send to All Users:**
```bash
php artisan cms:push-notification:send \
  --title="New Product Launch" \
  --message="Check out our latest products!" \
  --type="promotion"
```

**Send to Specific Platform:**
```bash
php artisan cms:push-notification:send \
  --title="iOS Update Available" \
  --message="Update your app to get the latest features" \
  --target="platform" \
  --target-value="ios"
```

**Send to User Type:**
```bash
php artisan cms:push-notification:send \
  --title="Admin Alert" \
  --message="System maintenance scheduled" \
  --target="user_type" \
  --target-value="admin"
```

**Send to Specific User:**
```bash
php artisan cms:push-notification:send \
  --title="Order Update" \
  --message="Your order has been shipped" \
  --target="user" \
  --target-value="123" \
  --user-type="customer"
```

**Schedule Notification:**
```bash
php artisan cms:push-notification:send \
  --title="Flash Sale" \
  --message="50% off everything!" \
  --schedule="2024-12-25 09:00:00"
```

**With Rich Content:**
```bash
php artisan cms:push-notification:send \
  --title="New Article" \
  --message="Read our latest blog post" \
  --action-url="/blog/latest-post" \
  --image-url="https://example.com/image.jpg" \
  --data='{"category":"blog","post_id":123}'
```

#### Options

- `--title` - Notification title (required)
- `--message` - Notification message (required)
- `--type` - Notification type (general, order, promotion, system) [default: general]
- `--target` - Target type (all, platform, user_type, user) [default: all]
- `--target-value` - Target value (required for platform, user_type, user targets)
- `--action-url` - URL to open when notification is clicked
- `--image-url` - Image URL for rich notifications
- `--data` - Additional JSON data
- `--schedule` - Schedule notification (Y-m-d H:i:s format)
- `--user-type` - User type when target is user (customer, admin) [default: customer]
- `--interactive` - Run in interactive mode

### 2. Process Scheduled Notifications Command

**Command:** `cms:push-notification:process-scheduled`

Process and send scheduled push notifications that are due.

#### Usage Examples

**Process All Due Notifications:**
```bash
php artisan cms:push-notification:process-scheduled
```

**Limit Processing:**
```bash
php artisan cms:push-notification:process-scheduled --limit=10
```

**Dry Run (Preview Only):**
```bash
php artisan cms:push-notification:process-scheduled --dry-run
```

#### Options

- `--limit` - Maximum number of notifications to process [default: 50]
- `--dry-run` - Show what would be processed without actually sending

#### Scheduling

You can add this command to your Laravel scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Process scheduled notifications every minute
    $schedule->command('cms:push-notification:process-scheduled')
             ->everyMinute()
             ->withoutOverlapping();
}
```

## Prerequisites

Before using these commands, ensure that:

1. **FCM Configuration** is set up in the admin panel:
   - Go to Settings → API Settings
   - Configure FCM Project ID
   - Upload Firebase service account JSON file

2. **Device Tokens** are registered:
   - Mobile apps should register device tokens via the API
   - Tokens are stored in the `device_tokens` table

3. **Database Tables** are migrated:
   - `push_notifications`
   - `push_notification_recipients`
   - `device_tokens`

## Notification Types

- `general` - General announcements
- `order` - Order-related notifications
- `promotion` - Marketing and promotional content
- `system` - System alerts and maintenance notices

## Target Types

- `all` - Send to all active device tokens
- `platform` - Send to specific platform (android/ios)
- `user_type` - Send to specific user type (customer/admin)
- `user` - Send to specific user by ID

## Error Handling

The commands include comprehensive error handling:

- Invalid FCM configuration
- Missing device tokens
- Invalid JSON data
- Network failures
- Invalid token cleanup

All errors are logged to the application log for debugging.

## Monitoring

Check notification status in the database:

```sql
-- View recent notifications
SELECT * FROM push_notifications ORDER BY created_at DESC LIMIT 10;

-- Check delivery rates
SELECT 
    title,
    sent_count,
    delivered_count,
    read_count,
    (delivered_count / sent_count * 100) as delivery_rate,
    (read_count / delivered_count * 100) as read_rate
FROM push_notifications 
WHERE sent_count > 0;
```
