<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Sākums') }}
                    </x-nav-link>

                    @if(auth()->check() && auth()->user()->isAdmin())
                        <x-nav-link :href="route('genres.index')" :active="request()->routeIs('genres.index')">
                            {{ __('Žanri') }}
                        </x-nav-link>
                        <x-nav-link :href="route('authors.index')" :active="request()->routeIs('authors.index')">
                            {{ __('Autori') }}
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('books.index')" :active="request()->routeIs('books.index')">
                        {{ __('Grāmatas') }}
                    </x-nav-link>
                    <x-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.index')">
                        {{ __('Pārdošana') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-responsive-nav-link>

            @if(auth()->check() && auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('genres.index')" :active="request()->routeIs('genres.index')">
                    {{ __('Genres') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('authors.index')" :active="request()->routeIs('authors.index')">
                    {{ __('Authors') }}
                </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('books.index')" :active="request()->routeIs('books.index')">
                {{ __('Books') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.index')">
                {{ __('Sales') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>


                </form>
            
        </div>
    </div>
</nav>
