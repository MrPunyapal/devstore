<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A developer hardware catalog for experimenting with Typesense search.">
    <title>DevStore · Typesense search lab</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-stone-50 text-slate-900 antialiased">{{ $slot }} @livewireScripts</body>
</html>
