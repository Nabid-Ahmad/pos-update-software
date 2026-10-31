<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="{{ $width ?? '38' }}" height="{{ $height ?? '38' }}">
    <defs>
        <linearGradient id="pdf_bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#E5252A"/>
            <stop offset="100%" stop-color="#B31417"/>
        </linearGradient>
    </defs>
    <!-- Background Tile -->
    <rect x="1.5" y="1.5" width="37" height="37" rx="8.5" fill="url(#pdf_bg)"/>
    <!-- Folded Document Page -->
    <path d="M12 7.5 H22 L27.5 13 V29.5 C27.5 30.3 26.8 31 26 31 H12 C11.2 31 10.5 30.3 10.5 29.5 V9 C10.5 8.2 11.2 7.5 12 7.5 Z" fill="#FFFFFF" fill-opacity="0.96"/>
    <path d="M22 7.5 V13 H27.5 Z" fill="#D9D9D9"/>
    <!-- PDF Banner -->
    <rect x="10.5" y="17.5" width="17" height="7.5" rx="1.5" fill="#E5252A"/>
    <text x="19" y="23.3" fill="#FFFFFF" font-size="5.5" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-weight="900" text-anchor="middle" letter-spacing="0.5">PDF</text>
    <!-- Modern Download Badge -->
    <circle cx="30" cy="30" r="7" fill="#FFFFFF"/>
    <path d="M30 26.5 V32 M27.8 30.3 L30 32.5 L32.2 30.3" fill="none" stroke="#E5252A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
