## notes 
- db: database/database.sqlite
- routes: routes/api.php
- run: php artisan serve 
- create a model and empty migration file:  php artisan make:model Product --migration
    - creates a Product.php file in app/Models
    - creates a migration in database/migrations
    - Then, you need to create a schema in that migration file 
- php artisan migrate
- import model in api.php: use App\Models\Product;
- /app/models/Product.php: add $fillable array, this array defines which fields can be used to insert/update data in the db
- php artisan make:controller ProductController --api
    - creates a controller in app/Http/Controllers 
- Sanctum setup: 
    - composer require laravel/sanctum
    - publish the Sanctum configuration and migration files:
        - php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
    - migrate
        - php artisan migrate
            - sanctum will create a db table for personal access tokens 
    - add SPA middleware in app/Http/kernel.php
    - initiate tokens for users: To begin issuing tokens for users, your User model should use the Laravel\Sanctum\HasApiTokens trait
-  php artisan make:controller AuthController
- update existing model/table: 
    - php artisan make:migration User
    - change fields within "up" function of most recent migration file
    - php artisan migrate
- refresh migrations: 
    - php artisan migrate:refresh


## citations 
1. Traversy Media, "Laravel 8 REST API With Sanctum Authentication", [link](https://www.youtube.com/watch?v=MT-GJQIY3EU)
2. Stack Overflow, "Call to a member function tokens() on null on Laravel Sanctum", [link](https://stackoverflow.com/questions/63351532/call-to-a-member-function-tokens-on-null-on-laravel-sanctum)
3. Code With Dary, "Eloquent One To One Relationship | Laravel For Beginners | Learn Laravel", [link](https://www.youtube.com/watch?v=sxGiIxf0Cgw)
4. Stack Overflow, "What does onDelete('cascade') mean?", [link](https://stackoverflow.com/questions/43094543/what-does-ondeletecascade-mean)
5. Yogesh Singh, "How to add Foreign key in Migration – Laravel", [link](https://makitweb.com/how-to-add-foreign-key-in-migration-laravel/)
6. The Codeholic, "Build and Deploy E-commerce Website with Laravel and Vue.js", [link](https://www.youtube.com/watch?v=jffKw_NMfnw) 
7. Stack Overflow, "Laravel migration default value", [link](https://stackoverflow.com/questions/37662955/laravel-migration-default-value) 
8. Stack Overflow, "Laravel Sanctum Auth issue", [link](https://stackoverflow.com/questions/62269976/laravel-sanctum-auth-issue)
9. "How does Laravel sanctum expire tokens?", [link](https://stackoverflow.com/questions/64117057/how-does-laravel-sanctum-expire-tokens)
10. Stack Overflow, "how to get property from api body in laravel?", [link], (https://stackoverflow.com/questions/65221422/how-to-get-property-from-api-body-in-laravel)