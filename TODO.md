# Role-Required Login Validation TODO

- [x] 1. Add role existence check in AuthController::login (block if !$user->roles()->exists())
- [x] 2. Test login with user having role (success)
 - [x] 3. Test login with user no role (403 'User has no assigned role. Access denied.')
 - [x] 4. Run php artisan test (core logic passes; unrelated StudentAuthenticationTest needs endpoint)
 - [x] 5. Complete
