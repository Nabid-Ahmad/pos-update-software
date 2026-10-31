<!-- Modern Executive Header Navbar -->
<header class="app-header">
    <!-- Brand Logo -->
    <a class="app-header__logo" href="{{ url('/home') }}">
        <div class="logo-badge-container">
            <div class="logo-badge-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M4 4h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H10.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1v2h3zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-10 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
            </div>
            <div>
                <span class="logo-title-text">NexPOS</span>
                <span class="logo-pro-pill">PRO</span>
            </div>
        </div>
    </a>

    <!-- Sidebar Toggle Button -->
    <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </a>

    @php
        $canSwitchCompany = true;
        if (isSuperAdmin()) {
            $userCompanies = \App\Company::orderBy('name')->pluck('name', 'id');
        } else {
            $roleCompanies = optional(optional(auth()->user()->user_role)->role)->companies;
            if ($roleCompanies && $roleCompanies->isNotEmpty()) {
                $userCompanies = $roleCompanies->pluck('name', 'id');
            } else {
                $userCompanies = auth()->user()->assignedCompanies()->pluck('companies.name', 'companies.id');
                if ($userCompanies->isEmpty() && auth()->user()->fk_company_id) {
                    $userCompanies = collect([auth()->user()->fk_company_id => auth()->user()->company->name ?? 'Company']);
                }
            }
        }
    @endphp

    @if ($canSwitchCompany && count($userCompanies) > 1)
        <div class="app-nav-item py-1 pr-2">
            <form action="{{ route('users.company_switch') }}" method="POST" id="headerCompanySwitchForm" class="d-inline-block m-0">
                @csrf
                <select name="company_id" id="headerCompanySelect" onchange="confirmCompanySwitch(this);" style="background-color: rgba(255, 255, 255, 0.08); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.15); font-weight: 600; cursor: pointer; border-radius: 8px; padding: 6px 12px; font-size: 13px; outline: none; transition: all 0.2s ease;">
                    @foreach ($userCompanies as $cId => $cName)
                        <option value="{{ $cId }}" {{ auth()->user()->fk_company_id == $cId ? 'selected' : '' }} style="color: #0f172a; background-color: #ffffff;">
                            🏢 {{ $cName }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <script>
            var activeCompanyId = "{{ auth()->user()->fk_company_id }}";
            function confirmCompanySwitch(selectElement) {
                var selectedVal = selectElement.value;
                var selectedText = selectElement.options[selectElement.selectedIndex].text.replace('🏢', '').trim();
                
                if (selectedVal == activeCompanyId) {
                    return;
                }
                
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire({
                        title: 'Switch Company?',
                        text: 'Do you want to switch to "' + selectedText + '"?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Switch!',
                        cancelButtonText: 'Cancel'
                    }).then(function(result) {
                        if (result.value || result.isConfirmed) {
                            document.getElementById('headerCompanySwitchForm').submit();
                        } else {
                            selectElement.value = activeCompanyId;
                        }
                    });
                } else if (confirm("Do you want to switch to \"" + selectedText + "\"?")) {
                    document.getElementById('headerCompanySwitchForm').submit();
                } else {
                    selectElement.value = activeCompanyId;
                }
            }
        </script>
    @else
        <div class="header-company-badge d-none d-sm-inline-flex">
            <span>Admin</span>
        </div>
    @endif

    <!-- Quick POS Actions -->
    <div class="d-none d-lg-flex align-items-center ml-2">
        @if (hasPermission('sales.create'))
            <a class="header-quick-action action-sale" href="{{ url('sales/create') }}" title="New Sale">
                <i class="fa fa-shopping-cart"></i> <span>Sale</span>
            </a>
        @endif
        @if (hasPermission('collections.create'))
            <a class="header-quick-action action-collection" href="{{ url('due-collections/create?type=customer') }}" title="Due Collection">
                <i class="fa fa-money"></i> <span>Collect</span>
            </a>
        @endif
        @if(hasPermission('sale.invoices.return'))
            <a class="header-quick-action action-purchase" href="{{ url('/sale-returns') }}" title="Sale Return">
                <i class="fa fa-reply"></i> <span>Return</span>
            </a>
        @endif
        @if(hasPermission('purchases.create'))
            <a class="header-quick-action action-purchase" href="{{ url('purchases/create') }}" title="New Purchase">
                <i class="fa fa-truck"></i> <span>Purchase</span>
            </a>
        @endif
    </div>

    <!-- Nav bar Right Menu-->
    <ul class="app-nav ml-auto d-flex align-items-center mb-0 pl-0">
        <!-- Notification Dropdown -->
        <li class="dropdown">
            <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Show notifications">
                <i class="fa fa-bell-o fa-lg" style="color: #94a3b8;"></i>
                <sup class="badge badge-danger not-badge" style="border-radius: 50%; font-size: 9px; padding: 2px 5px;"></sup>
            </a>
            <ul class="app-notification dropdown-menu dropdown-menu-right">
                <li class="app-notification__title titleCount" style="background: #1e293b; color: #fff; font-weight: 600; border-radius: 8px 8px 0 0;"></li>
                <div class="app-notification__content temp" style="max-height: 240px; overflow-y: auto;">
                </div>
                <li class="app-notification__footer" style="background: #1e293b; border-radius: 0 0 8px 8px;">
                    <a href="{{ url('reports/product_alert') }}" style="color: #38bdf8; font-weight: 500;">See All Alerts</a>
                </li>
            </ul>
        </li>

        <!-- User Profile Menu -->
        <li class="dropdown ml-2">
            <a class="app-nav__item p-1" style="text-decoration:none;" href="#" data-toggle="dropdown" aria-label="Open Profile Menu">
                <div class="user-profile-badge">
                    <span class="user-avatar-mini">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                    <span style="font-size: 13px; font-weight: 600; color: #f8fafc;">{{ ucfirst(Auth::user()->name ?? 'Admin') }}</span>
                    <i class="fa fa-angle-down ml-1" style="color: #94a3b8; font-size: 12px;"></i>
                </div>
            </a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right" style="min-width: 180px;">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" title="Edit" href="{{ route('users.edit', Auth::user()->id) }}">
                        <i class="fa fa-user-circle mr-2 text-primary" style="font-size: 16px;"></i> My Profile
                    </a>
                </li>
                <div class="dropdown-divider" style="border-color: rgba(255,255,255,0.08);"></div>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa fa-sign-out mr-2" style="font-size: 16px;"></i> Logout
                    </a>
                </li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </ul>
        </li>
    </ul>
</header>
