{{-- Данни за Google на началната страница: бизнесът (ProfessionalService) и сайтът (WebSite), от App\Support\Schema. --}}
<script type="application/ld+json">{!! json_encode(\App\Support\Schema::organization(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
<script type="application/ld+json">{!! json_encode(\App\Support\Schema::website(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
