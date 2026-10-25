{{-- Consent Mode v2: everything except strictly necessary storage is denied until the visitor chooses in the cookie banner. --}}
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('consent', 'default', {ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied', functionality_storage: 'granted', security_storage: 'granted', wait_for_update: 500});
gtag('set', 'ads_data_redaction', true);
(function () {
    try {
        var c = JSON.parse(localStorage.getItem('creatium-consent'));
        if (c && Date.now() - c.at < 31536000000) {
            gtag('consent', 'update', {analytics_storage: c.analytics ? 'granted' : 'denied', ad_storage: c.marketing ? 'granted' : 'denied', ad_user_data: c.marketing ? 'granted' : 'denied', ad_personalization: c.marketing ? 'granted' : 'denied'});
        }
    } catch (e) {}
})();
</script>
<!-- Google Tag Manager -->
{{-- Loaded 2.5 s after the page has loaded, or at the visitor's first scroll, tap or key press, so the tags don't compete with the
     first paint on slow phones (PageSpeed). Events pushed before that, such as generate_lead, wait in dataLayer and are sent when it loads. --}}
<script>(function(w,d,s,l,i){var done=false,events=['scroll','pointerdown','keydown','touchstart'];
function load(){if(done)return;done=true;events.forEach(function(e){w.removeEventListener(e,load,{passive:true});});
w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);}
events.forEach(function(e){w.addEventListener(e,load,{passive:true});});
if(d.readyState==='complete'){setTimeout(load,2500);}else{w.addEventListener('load',function(){setTimeout(load,2500);});}
})(window,document,'script','dataLayer',@json(config('creatium.gtm_id')));</script>
<!-- End Google Tag Manager -->
@if(session('lead_created'))
    <script>dataLayer.push({event: 'generate_lead', lead_source: 'contact_form', lead_service: @json((string) session('lead_service'))});</script>
@endif
