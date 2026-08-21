import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/assets/css/pages/login.css",
                "resources/assets/css/pages/dashboard.css",
                "resources/assets/css/pages/crud.css",
                "resources/assets/css/components/sidebar.css",
                "resources/assets/js/app.js",
            ],
            refresh: true,
        }),
    ],
});