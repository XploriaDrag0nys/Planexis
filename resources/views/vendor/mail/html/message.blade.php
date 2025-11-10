<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ config('app.name') }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy auto quand un bouton est présent --}}
    @isset($actionText)
        <x-slot:subcopy>
            <x-mail::subcopy>
                Si le bouton « {{ $actionText }} » ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
                <span class="break-all">
                    <a href="{{ $actionUrl }}">{{ $displayableActionUrl ?? $actionUrl }}</a>
                </span>
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
