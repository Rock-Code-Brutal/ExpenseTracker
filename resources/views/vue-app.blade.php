<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Expense Tracker') }}</title>
    <meta name="description" content="Modern expense tracking application built with Laravel and Vue 3">
    <meta name="keywords" content="expense tracker, budget, finance, laravel, vue">
    <meta name="author" content="Expense Tracker App">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: transparent;
        }
    </style>
</head>
<body>
    <div id="app"></div>
</body>
</html>
