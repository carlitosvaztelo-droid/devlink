# DevLink Debugging Guide

## Issues Fixed

### 1. Registration Not Working
**Problem**: Users couldn't register because the form wasn't calling the API.

**Solution**: 
- Updated `auth.html` to make actual API calls to `api/auth.php?action=register`
- Added proper validation for all form fields
- Added support for email, cedula, and telefono as identification methods
- Shows success/error messages to the user

### 2. Login Not Calling Database
**Problem**: Login was just redirecting without validating credentials.

**Solution**:
- Updated `auth.html` to make API calls to `api/auth.php?action=login`
- Enhanced `api/auth.php` to support login via email, cedula, or telefono
- Fixed password verification (was missing `contraseña` field in SELECT)
- Session-based authentication now works properly

### 3. Dashboard Not Loading Database Data
**Problem**: Dashboard showed "Cargando..." placeholders forever.

**Solution**:
- Updated `APIClient` to include `credentials: 'include'` for session support
- Enhanced `dashboard-loader.js` with better logging and error handling
- Added fallback UI when API calls fail
- Fixed API client to properly handle and log errors

## Testing the fixes

### Test 1: Registration
1. Go to `auth.html`
2. Click "Registrarse"
3. Fill in form with:
   - Nombre: Test
   - Apellido: Usuario
   - Cédula: V-12345678
   - Contraseña: testpass123
   - Confirmar: testpass123
4. Click "Crear Cuenta"
5. Check browser console for logs with `[v0]` prefix
6. Should redirect to dashboard if successful

### Test 2: Login
1. Go to `auth.html`
2. Select "Cliente" or "Dev"
3. Enter credentials from registration
4. Click "Iniciar Sesión"
5. Should show dashboard with data

### Test 3: Dashboard Data Loading
1. After login, open browser DevTools console
2. Look for logs starting with `[DashboardLoader]` and `[APIClient]`
3. Check that projects/proposals/contracts are being fetched
4. Stats should update in real-time

## Debugging Tips

### Check Console Logs
All debug information is logged with prefixes:
- `[v0]` - Form submission logs
- `[DashboardLoader]` - Dashboard data loading
- `[APIClient]` - API request/response logs
- `[v0] Error` - Error messages

### Common Issues

**Issue: "Error de conexión: Método no permitido"**
- Make sure you're using POST for registration/login
- Check that API endpoints exist in `/api/` directory

**Issue: "Credenciales inválidas"**
- Verify email or cedula matches what's in database
- Check password is correct (hashed with BCRYPT)
- Ensure user record has `activo = 1`

**Issue: "Dashboard shows error loading data"**
- Check browser console for API error messages
- Verify database connection in `config/database.php`
- Make sure API files exist and return JSON

### Database Verification
Run these SQL queries to verify:
```sql
-- Check if users table has data
SELECT * FROM usuarios LIMIT 5;

-- Check specific user
SELECT id, nombre, email, cedula, telefono, tipo, activo 
FROM usuarios 
WHERE email = 'user@example.com';
```

## API Endpoints Status

Check these are working:
- `api/auth.php?action=register` - POST
- `api/auth.php?action=login` - POST
- `api/auth.php?action=get-current-user` - GET
- `api/projects.php?action=list` - GET
- `api/proposals.php?action=list` - GET
- `api/contracts.php?action=list` - GET

## Session Management

The system now properly:
1. Creates sessions on successful login/registration
2. Stores user_id, user_type, and email in $_SESSION
3. Uses sessions for API authentication
4. Browser automatically sends cookies with each request

## Next Steps

1. Clear browser cache/cookies if tests don't work
2. Check database has proper tables and columns
3. Monitor console logs during user actions
4. Test each section of dashboard (Search, Projects, etc.)
