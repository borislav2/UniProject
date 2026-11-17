{{-- Общата глава на всички имейли в стила на дизайн Г (Onest за текст, Geologica за заглавия, JetBrains Mono за малките етикети).
     Уеб шрифтове в пощата не са сигурни и не зареждаме нищо отвън (никакви външни заявки), затова всеки стил носи резервен списък:
     Onest, после Segoe UI, Helvetica, Arial. Тъмният режим е само тук: правилата хващат цветовете от inline стиловете, така че шаблоните не носят втори набор цветове.
     Цветовете в шаблоните са точно тези: фон #ECEEF0, карта #F7F8F9, панел #FFFFFF, мастило #0C0E12, текст #4A5058 и #5A6068, линия #D9DBDE, акцент #E8431B (за малък текст #C4300F). --}}
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<style type="text/css">
:root { color-scheme: light dark; supported-color-schemes: light dark; }
.g-logo-d { display: none; max-height: 0; overflow: hidden; mso-hide: all; }
@media (max-width: 480px) {
    .g-pad { padding-left: 20px !important; padding-right: 20px !important; }
    .g-col { display: block !important; width: 100% !important; box-sizing: border-box; }
    .g-h1 { font-size: 24px !important; }
}
@media (prefers-color-scheme: dark) {
    body, [style*="background:#ECEEF0"] { background: #0B0C0F !important; }
    [style*="background:#F7F8F9"] { background: #12141A !important; }
    [style*="background:#FFFFFF"] { background: #0B0C0F !important; }
    [style*="background:#0C0E12"] { background: #1B1E25 !important; }
    [style*="color:#0C0E12"] { color: #ECEEF0 !important; }
    [style*="color:#4A5058"] { color: #B4B9C1 !important; }
    [style*="color:#5A6068"], [style*="color:#6B7178"] { color: #9096A0 !important; }
    [style*="color:#C4300F"] { color: #FF5A33 !important; }
    [style*="#D9DBDE"] { border-color: #2A2D34 !important; }
    [style*="background:#D9DBDE"] { background: #2A2D34 !important; }
    .g-logo-l { display: none !important; max-height: 0 !important; overflow: hidden !important; }
    .g-logo-d { display: block !important; max-height: none !important; overflow: visible !important; }
    .g-btn { background: #ECEEF0 !important; }
    .g-btn a { color: #0C0E12 !important; }
    .g-btn-line { border-color: #ECEEF0 !important; }
}
</style>
