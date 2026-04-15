# TODO: Fix Excel Import Duplicates

## Approved Plan Steps:
- [x] Step 1: Add `import()` method to ProductController.php using ProductsImport for file uploads.
- [x] Step 2: Replace `updateOrCreate` with `Product::create()` in `importMapping()` method.

- [x] Step 3: Clear routes cache: `php artisan route:clear`
- [ ] Step 4: Test import with duplicate ref Excel → verify multiple records created.
- [ ] Complete

