# Tonka/Spark 🌩️

The secure bridge between your [**Tonka Framework**](https://clicalmani.github.io/tonka) backend and your frontend router.

`Spark` is the server-side package responsible for exposing, filtering, and formatting your named routes for the `tonka-router` package. It features dynamic access control through **Policies**, allowing you to hide sensitive routes from unauthorized users effortlessly.

## 🌟 Why use Spark?

Exposing *all* your backend routes to the browser is a security risk. `Spark` solves this by giving you fine-grained control over what is visible to the frontend, leveraging your existing **Policy** classes to determine visibility based on the current user's role.

## ✨ Features

*   🛡️ **Dynamic Access Control**: Integrate with your existing Policy classes to show/hide routes (e.g., hide `admin.*` from regular users) automatically.
*   📦 **JSON Output**: Generates a structured JSON object ready for JavaScript consumption.
*   🖥️ **Template Directive**: Injects routes directly into your HTML (via a `data-routes` attribute or global variable).
*   🔌 **Groups**: Organize your routes by group (e.g., `public`, `auth`) and expose them selectively for better performance.
*   🚀 **Flexible Filtering**: Use Whitelist (`only`) or Blacklist (`except`) modes with wildcard support.

## ⚙️ Configuration

Publish the configuration file:

```bash
php tonka spark:config
```

This will create `config/spark.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Route Filtering (Whitelist Mode)
    |--------------------------------------------------------------------------
    | By default, no routes are exposed for security. 
    | You must explicitly allow routes.
    |
    | You can attach a 'policy' class to any rule. If provided, Spark will 
    | instantiate it and call the authorize() method. If it returns false, 
    | the route will be hidden from the JSON output.
    */
    'only' => [
        // Public routes (accessible to everyone)
        [
            'name' => 'home',
            'policy' => null
        ],
        [
            'name' => 'posts.*', // Wildcard support
            'policy' => null
        ],

        // Protected routes (Only visible if the Policy returns true)
        [
            'name' => 'users.profile',
            'policy' => \App\Contracts\Spark\UserContract::class
        ],
        
        // Admin routes (Only visible if AdminContract authorizes)
        [
            'name' => 'admin.*',
            'policy' => \App\Contracts\Spark\AdminContract::class
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Filtering (Blacklist Mode)
    |--------------------------------------------------------------------------
    | Use this to expose everything EXCEPT specific patterns.
    | Policies are also supported here to enforce further restrictions.
    */
    // 'except' => [
    //     [
    //         'name' => '_debugbar.*',
    //         'policy' => \App\Contracts\Spark\SuperAdminContract::class
    //     ],
    // ],

    /*
    |--------------------------------------------------------------------------
    | Groups
    |--------------------------------------------------------------------------
    | Define route groups for selective exposure via @routes('group_name').
    */
    'groups' => [
        'public' => ['home', 'about', 'contact'],
        'auth' => ['dashboard', 'profile', 'settings'],
    ],
];
```

### 🧭 Groups vs. Policies: Why Use Both?

You might wonder: *"If Policies handle security, are Groups necessary?"*

The short answer is **yes**. They serve distinct but complementary purposes:

*   **Policies (The "Who am I?"):** Handle **Security**. They determine if the current user is *authorized* to see a specific route (e.g., hiding `admin.*` from regular users).
*   **Groups (The "Where am I?"):** Handle **Context & Performance**. They determine which routes are *relevant* to the current page (e.g., only sending auth routes to the Dashboard page, not the Login page).

**Why combine them?**

1.  **Performance:** Without groups, sending *all* authorized routes (which could be hundreds) to a simple login page is wasteful. Using groups keeps the JSON payload small and fast.
2.  **Maintainability:** Groups allow you to update route lists in one place (`config/spark.php`) instead of hardcoding arrays in every Blade file.

## 🚀 Usage

### 1. Injection in Views (Recommended)

Use the `@routes` directive in your main layout.

```html
<!DOCTYPE html>
<html>
<head>
    <!-- ... -->
</head>
<body>
    @routes('auth') <!-- Or simple @routes (without group)-->
    @inertia
</body>
</html>
```

**How it works with Policies:**
When the page loads, `Spark` iterates through your configuration. For every route matching your rules, it checks the associated `Policy`.
*   If the **Policy** authorizes the request → The route is included in the JSON.
*   If the **Policy** denies the request → The route is excluded.
*   If no **Policy** is defined → The route is included (default behavior).

### 2. File Generation

Generate a static routes file for SPAs:

```bash
php tonka spark:generate
```

This creates a `routes.json` file reflecting the current user's permissions.

### 3. API Endpoint

For dynamic fetching:

```php
Route::get('/api/spark-routes', function () {
    return \Tonka\Spark\Spark::toJson();
});
```

## 🔐 Security Best Practices

1.  **Use Policies for Sensitive Routes**: Do not rely solely on frontend hiding. Use the `policy` key to ensure backend logic prevents unauthorized routes from ever reaching the browser.
2.  **Avoid `except` if possible**: Whitelisting (`only`) is generally safer than blacklisting (`except`) for API exposure.
3.  **Validate Policy Methods**: Ensure your Policy classes have a public `authorize()` method that returns a boolean.

## 📚 Integration with Tonka Router

Once exposed, your frontend consumes the routes seamlessly:

```javascript
import { route } from 'tonka-router';

// This link will only be generated if the 'admin.users' route was authorized by the backend Policy
const url = route('admin.users', { id: 1 }); // Or simply route('admin.users', [1])
```