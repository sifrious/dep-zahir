<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Accounts</title>
    @vite('resources/css/app.css')
</head>
<body class="burg-paper">
<header class="site-shell site-nav">
    <a class="burg-wordmark" href="{{ route('home') }}">accounts</a>
    <nav class="site-nav__links" aria-label="Primary">
        <a href="{{ route('home') }}">Products</a>
        <a href="{{ route('pricing') }}">Pricing</a>
        <a href="{{ route('billing') }}">Billing</a>
        <a href="{{ route('trust.index') }}">Trust center</a>
    </nav>
</header>
<main class="site-shell site-main site-stack">
    {{ $slot }}
</main>
<footer class="site-shell site-footer">
    <small>Accounts publishes product, billing, legal, and compliance information for connected products.</small>
</footer>
</body>
</html>
