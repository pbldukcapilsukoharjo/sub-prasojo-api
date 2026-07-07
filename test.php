<?php require "vendor/autoload.php"; require "bootstrap/app.php"; $app->make("Illuminate\Contracts\Console\Kernel")->bootstrap(); echo optional(null)?->format("Y-m-d"); echo "Done";
