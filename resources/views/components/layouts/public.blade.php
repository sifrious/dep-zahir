<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Accounts</title>
    <style>
        :root { color-scheme: light dark; font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.6; }
        body { margin: 0; }
        header, main, footer { width: min(50rem, calc(100% - 2rem)); margin-inline: auto; }
        header, footer { padding-block: 1.5rem; }
        main { min-height: 70vh; }
        a { color: inherit; }
        ul { padding: 0; list-style: none; }
        li { border-block-start: 1px solid color-mix(in srgb, currentColor 20%, transparent); padding-block: 1rem; }
        .meta { opacity: .72; }
    </style>
</head>
<body>
<header>
    <a href="{{ route('trust.index') }}">Accounts trust center</a>
</header>
<main>
    {{ $slot }}
</main>
<footer>
    <small>Accounts publishes approved legal and compliance information for connected products.</small>
</footer>
</body>
</html>
