# Testing Candle

This document provides information on how to quickly set up test data for development and testing of the Candle package.

## Command Line Testing

The package includes a command for creating test users that only runs in the `testing` or `local` environments:

```bash
# Create a test user with default values
php artisan analytics:create-test-user

# Create a test user with custom values
php artisan analytics:create-test-user --email=admin@example.com --password=secret123 --name="Admin User"
```

## Testing Routes

The package provides several routes for quickly setting up test data in the `testing` or `local` environments. These routes are prefixed with `/testing/`.

### Available Routes

#### Create a Test User

```
GET /testing/create-test-user
```

**Optional Parameters:**
- `name` - The name of the test user (default: "Test User")
- `email` - The email of the test user (default: "test@example.com")
- `password` - The password of the test user (default: "password")

**Example:**
```
GET /testing/create-test-user?email=admin@example.com&password=secret123&name=Admin
```

#### Create a Test Site with API Key

```
GET /testing/create-test-site
```

**Required Parameters:**
- `user_id` - The ID of the user who will own the site

**Optional Parameters:**
- `name` - The name of the test site (default: "Test Site")
- `domain` - The domain of the test site (default: "example.com")

**Example:**
```
GET /testing/create-test-site?user_id=1&name=My%20Blog&domain=blog.example.com
```

#### Complete Test Environment Setup

This route creates a test user, site, and API key in one call:

```
GET /testing/setup-test-environment
```

**Optional Parameters:**
- `name` - The name of the test user (default: "Test User")
- `email` - The email of the test user (default: "test@example.com")
- `password` - The password of the test user (default: "password")
- `site_name` - The name of the test site (default: "Test Site")
- `domain` - The domain of the test site (default: "example.com")

**Example:**
```
GET /testing/setup-test-environment?email=admin@example.com&site_name=Production%20Site
```

#### Clean Up Test Environment

This route deletes all test data created by the other routes:

```
GET /testing/cleanup-test-environment?confirm=yes-i-am-sure
```

**Required Parameters:**
- `confirm` - Must be set to "yes-i-am-sure" to confirm deletion

**Optional Parameters:**
- `email` - Specific test user email to delete in addition to the default test@example.com

## Using in PHPUnit Tests

You can use these commands and routes in your PHPUnit tests to set up test data:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CandleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user using the command
        Artisan::call('analytics:create-test-user', [
            '--email' => 'test@example.com',
            '--password' => 'password',
            '--name' => 'Test User'
        ]);

        // Or use the testing route
        $response = $this->get('/testing/setup-test-environment');
        $testData = $response->json();

        // Now you have a user, site, and API key for testing
        $this->testUserId = $testData['user']['id'];
        $this->testSiteId = $testData['site']['id'];
        $this->testApiKey = $testData['api_key']['key'];
    }

    /** @test */
    public function can_track_events()
    {
        // Your test code here
    }
}
```

## Security Warning

These testing routes and commands are only available in the `testing` and `local` environments for security reasons. If they were available in production, they could be used to create unauthorized test accounts.

Always ensure your production environment is not set to `testing` or `local`.
