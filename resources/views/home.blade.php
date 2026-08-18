<x-layouts.app :title="config('app.name')">
    <div id="app" data-page="home" data-props="{{ json_encode(['username' => auth()->user()->username]) }}"></div>
</x-layouts.app>
