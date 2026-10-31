<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="{{ $width ?? '38' }}" height="{{ $height ?? '38' }}">
    <defs>
        <linearGradient id="xls_bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#107C41"/>
            <stop offset="100%" stop-color="#085226"/>
        </linearGradient>
        <linearGradient id="xls_card" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#188A4A"/>
            <stop offset="100%" stop-color="#0E6333"/>
        </linearGradient>
    </defs>
    <!-- Background Tile -->
    <rect x="1.5" y="1.5" width="37" height="37" rx="8.5" fill="url(#xls_bg)"/>
    <!-- Spreadsheet Grid Preview -->
    <rect x="18" y="7.5" width="14" height="23" rx="2" fill="#FFFFFF" fill-opacity="0.96"/>
    <rect x="20.5" y="10.5" width="9" height="2" rx="0.5" fill="#107C41"/>
    <line x1="20.5" y1="15" x2="29.5" y2="15" stroke="#107C41" stroke-width="1.3" stroke-linecap="round" stroke-opacity="0.5"/>
    <line x1="20.5" y1="18.5" x2="29.5" y2="18.5" stroke="#107C41" stroke-width="1.3" stroke-linecap="round" stroke-opacity="0.5"/>
    <line x1="20.5" y1="22" x2="29.5" y2="22" stroke="#107C41" stroke-width="1.3" stroke-linecap="round" stroke-opacity="0.5"/>
    <!-- Floating 'X' Tile -->
    <rect x="6" y="9.5" width="16" height="18" rx="3.5" fill="url(#xls_card)" stroke="rgba(255,255,255,0.2)" stroke-width="0.8"/>
    <path d="M10.5 14.5 L17.5 22.5 M17.5 14.5 L10.5 22.5" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round"/>
    <!-- Modern Download Badge -->
    <circle cx="30" cy="30" r="7" fill="#FFFFFF"/>
    <path d="M30 26.5 V32 M27.8 30.3 L30 32.5 L32.2 30.3" fill="none" stroke="#107C41" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
