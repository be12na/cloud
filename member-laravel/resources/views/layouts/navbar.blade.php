<nav class="m-nav">
    <div class="wrap">
        <a href="{{ url('/') }}" class="m-brand">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>
            {{ config('app.name') }}
        </a>

        <button class="m-toggle" onclick="this.nextElementSibling.classList.toggle('open')"><i class="bi bi-list"></i></button>

        <ul class="m-links">
            @guest
                <li><a href="{{ route('member.login') }}" class="{{ request()->routeIs('member.login') ? 'active' : '' }}"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                <li class="sep"></li>
                <li><a href="{{ route('member.register') }}" class="{{ request()->routeIs('member.register') ? 'active' : '' }}"><i class="bi bi-person-plus"></i> Register</a></li>
            @else
                <li><a href="{{ route('member.dashboard') }}" class="{{ request()->routeIs('member.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="sep"></li>
                <li>
                    <div class="m-user" onclick="document.getElementById('navDd').classList.toggle('show')">
                        <div class="m-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        {{ Auth::user()->name }}
                        <i class="bi bi-chevron-down" style="font-size:.7rem;opacity:.6"></i>
                        <div class="m-dd" id="navDd">
                            <div class="dd-label">{{ Auth::user()->email }}</div>
                            <div class="dd-line"></div>
                            <a href="{{ route('member.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                            <div class="dd-line"></div>
                            <form method="POST" action="{{ route('member.logout') }}">
                                @csrf
                                <button type="submit" class="dd-red"><i class="bi bi-box-arrow-right"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                </li>
            @endguest
        </ul>
    </div>
</nav>
