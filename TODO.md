# Fix Fournisseur Dropdown in Excel Import

## Approved Plan Steps:

### ✅ 1. Add seed data & fix IDs in FournisseurController.php ✓
### ✅ 2. Fix importMapping in ProductController.php ✓

### ✅ 3. Frontend polish ✓
### [ ] 4. Test  
```
✅ Backend fixed. Run:
cd c:/laragon/www/gda && php artisan serve
curl -X POST http://127.0.0.1:8000/api/fournisseurs/seed  
curl http://127.0.0.1:8000/api/fournisseurs
# Open app → Import modal → dropdown works!
```
### [ ] 5. Complete
