# Fix Auth Registration Issue (Sanctum Trait Not Found)

## Status: In Progress

### Step 1: [COMPLETED] ✅ Create TODO.md with task steps
### Step 2: [COMPLETED] ✅ Edit bootstrap/providers.php to register SanctumServiceProvider
### Step 3: [COMPLETED] ✅ Run composer dump-autoload
### Step 4: [COMPLETED] ✅ Run php artisan optimize:clear
### Step 5: [COMPLETED] ✅ Inspected routes/api.php and AuthController.php - registration available at POST /api/register (uses createToken requiring HasApiTokens)
### Step 6: [COMPLETED] ✅ Fixed User.php trait namespace (Illuminate\Sanctum → Laravel\Sanctum\HasApiTokens) + cleared caches + fixed bootstrap/cache permissions
