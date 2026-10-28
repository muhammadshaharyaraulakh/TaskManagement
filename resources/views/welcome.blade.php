<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-white p-4 font-sans text-black antialiased sm:p-6">
        <main class="w-full max-w-lg border-4 border-black bg-white p-6 shadow-[10px_10px_0_0_#000] sm:p-10">
            <h1 class="text-3xl font-black leading-tight sm:text-5xl">Welcome to Task Manager</h1>
            <p class="mt-4 text-base font-medium sm:text-lg">Organize your projects and prioritize your tasks.</p>

            <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="border-2 border-black bg-black px-6 py-3 text-center font-bold text-white shadow-[4px_4px_0_0_#000] transition hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[6px_6px_0_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                        Go to dashboard
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                            class="border-2 border-black bg-black px-6 py-3 text-center font-bold text-white shadow-[4px_4px_0_0_#000] transition hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[6px_6px_0_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                            Log in
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="border-2 border-black bg-white px-6 py-3 text-center font-bold text-black shadow-[4px_4px_0_0_#000] transition hover:bg-black hover:text-white active:translate-x-1 active:translate-y-1 active:shadow-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black">
                            Sign up
                        </a>
                    @endif
                @endauth
            </div>
        </main>
    </body>
</html>