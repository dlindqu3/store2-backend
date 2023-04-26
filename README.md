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


## citations 
1. Traversy Media, "Laravel 8 REST API With Sanctum Authentication", [link](https://www.youtube.com/watch?v=MT-GJQIY3EU)