<?php

header("Content-Type: application/json");

// Config
require_once __DIR__ . "/config/database.php";

// Utils
require_once __DIR__ . "/utils/Response.php";
require_once __DIR__ . "/utils/JwtHandler.php";

// Middleware
require_once __DIR__ . "/middleware/AuthMiddleware.php";

// Models
require_once __DIR__ . "/models/User.php";
require_once __DIR__ . "/models/Product.php";

// Controllers
require_once __DIR__ . "/controllers/AuthController.php";
require_once __DIR__ . "/controllers/UserController.php";
require_once __DIR__ . "/controllers/ProductController.php";

// Routes
require_once __DIR__ . "/routes/api.php";
