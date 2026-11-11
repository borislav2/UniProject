<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (ноември 2026): „навсякъде където има тирета и двуеточия искам да се махнат“. Видимият текст на сайта
 * няма тире (–, —, „ - “) и двоеточие (освен в часове и адреси). В config текстовете са пренаписани; тук се сменят и
 * админ редакциите в `content_blocks`, но само низове, които са ТОЧНО стар текст по подразбиране (цял низ, всеки език).
 * Собствените редакции на собствениците не се пипат. Всеки променен запис първо се копира в `<ключ>:backup-2026-11-08-g`
 * (съществуващо копие не се презаписва).
 */
class PlainCopy2026
{
    public const KEYS = ['home_problem',  'home_trust',  'services_page',  'plans',  'questions',  'about',  'team',  'demos'];

    public const BACKUP = ':backup-2026-11-08-g';

    /** Стар текст по подразбиране => нов текст (български и английски). */
    public const REPLACE = [
        'Програмирането не спира с работата: уча софтуерно инженерство във Великотърновския университет. Пред вас обаче говоря без технически думи. Казвам ви какво сме направили и защо е важно за бизнеса ви.'
            => 'Програмирането не спира с работата. Уча софтуерно инженерство във Великотърновския университет. Пред вас обаче говоря без технически думи. Казвам ви какво сме направили и защо е важно за бизнеса ви.',
        'Работата ми започва с въпроса как ви търсят клиентите. Подреждам профила ви в Google, пиша текстовете, пускам рекламите в Google и Meta и изпращам имейлите. Гледам и дали ви откриват асистенти като ChatGPT. Накрая проверявам най-важното: идват ли обаждания и запитвания.'
            => 'Работата ми започва с въпроса как ви търсят клиентите. Подреждам профила ви в Google, пиша текстовете, пускам рекламите в Google и Meta и изпращам имейлите. Гледам и дали ви откриват асистенти като ChatGPT. Накрая проверявам най-важното, а именно дали идват обаждания и запитвания.',
        'За да носи реални резултати, един сайт се нуждае от постоянна грижа и развитие, а не просто да стои като визитка. Месечният абонамент покрива денонощната техническа сигурност, хостинга, редовните архиви и обновления, както и маркетинговите дейности: следене на посещенията в Google Analytics, грижа за профила ви в Google Maps и за това да ви разбират AI асистентите (ChatGPT, Gemini), имейл комуникацията и текущите промени по сайта.'
            => 'За да носи реални резултати, един сайт се нуждае от постоянна грижа и развитие, а не просто да стои като визитка. Месечният абонамент покрива денонощната техническа сигурност, хостинга, редовните архиви и обновления, както и маркетинговите дейности. Това са следене на посещенията в Google Analytics, грижа за профила ви в Google Maps и за това да ви разбират AI асистентите (ChatGPT, Gemini), имейл комуникацията и текущите промени по сайта.',
        'Стартовата цена се плаща на две части. За Стартов двигател: €100 при заявка за оферта и демо и останалите €300 при съгласие. За Активен маркетинг и растеж: €200 при заявка за оферта и демо и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите. При Индивидуален маркетинг също има капаро преди офертата и демото: сумата се уточнява в индивидуалната оферта и също не се връща. Абонаментът започва от втория месец.'
            => 'Стартовата цена се плаща на две части. За Стартов двигател плащате €100 при заявка за оферта и демо и останалите €300 при съгласие. За Активен маркетинг и растеж плащате €200 при заявка за оферта и демо и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите. При Индивидуален маркетинг също има капаро преди офертата и демото. Сумата се уточнява в индивидуалната оферта и също не се връща. Абонаментът започва от втория месец.',
        'Когато уеб сайтът и маркетингът се възлагат на различни изпълнители, комуникацията често се забавя и се получават разминавания – маркетинговата стратегия се сблъсква с технически ограничения на платформата, а сайтът липсва проследяващи кодове и правилна структура. Когато се проектират интегрирано, уеб сайтът се изгражда с вече внедрена аналитика, подготвени имейл фунии и SEO архитектура от самото начало.'
            => 'Когато уеб сайтът и маркетингът се възлагат на различни изпълнители, комуникацията често се забавя и се получават разминавания. Маркетинговата стратегия се сблъсква с технически ограничения на платформата, а сайтът липсва проследяващи кодове и правилна структура. Когато се проектират интегрирано, уеб сайтът се изгражда с вече внедрена аналитика, подготвени имейл фунии и SEO архитектура от самото начало.',
        'Да. Сайтовете се изграждат с удобен и лесен за управление административен панел. В допълнение, вашият абонамент включва и нашата регулярна поддръжка – така че при нужда от промяна в цени, оферти или текстове, можем да я извършим бързо вместо вас.'
            => 'Да. Сайтовете се изграждат с удобен и лесен за управление административен панел. В допълнение, вашият абонамент включва и нашата регулярна поддръжка. Така при нужда от промяна в цени, оферти или текстове можем да я извършим бързо вместо вас.',
        'Виждахме го отново и отново. Бизнесът плаща на един човек за сайт и на друг за реклама. Всеки си върши своята част, но никой не отговаря на простия въпрос: идват ли клиенти?'
            => 'Виждахме го отново и отново. Бизнесът плаща на един човек за сайт и на друг за реклама. Всеки си върши своята част, но никой не отговаря на простия въпрос дали идват клиенти.',
        'Затова работим като един екип. Борислав прави сайта и техническата част, Владимир се грижи хората да ви намират и да ви пишат. Отговаряме за целия път: от първото посещение до първото запитване, и след това.'
            => 'Затова работим като един екип. Борислав прави сайта и техническата част, Владимир се грижи хората да ви намират и да ви пишат. Отговаряме за целия път, от първото посещение до първото запитване, и след това.',
        'Клиентът възприема бранда ви като едно цяло – от първата среща в мрежата до финалната сделка. Затова развиваме дигиталната ви стратегия интегрирано и свързваме дизайна, технологията и маркетинга в единен механизъм.'
            => 'Клиентът възприема бранда ви като едно цяло, от първата среща в мрежата до финалната сделка. Затова развиваме дигиталната ви стратегия интегрирано и свързваме дизайна, технологията и маркетинга в единен механизъм.',
        'Бързината е част от доверието: клиентът не чака, не се съмнява и стига до вас.'
            => 'Бързината е част от доверието. Клиентът не чака, не се съмнява и стига до вас.',
        "Поемаме целия процес: от кодирането на сайта и свързването на формите към вашия имейл или CRM до пълната настройка на рекламите.\n\nНосим отговорност за цялата система, от влизането на посетителя до първото запитване."
            => "Поемаме целия процес, от кодирането на сайта и свързването на формите към вашия имейл или CRM до пълната настройка на рекламите.\n\nНосим отговорност за цялата система, от влизането на посетителя до първото запитване.",
        'Рекламните кодове са настроени: измервате всяко запитване и продажба'
            => 'Рекламните кодове са настроени и измервате всяко запитване и продажба',
        'Успехът не приключва с първата продажба. Нашият двигател обхваща целия път на клиента – от откриването на бранда до момента, в който той става постоянен и ви препоръчва на други. Изключете някое ниво вдясно и вижте къде се прекъсва веригата.'
            => 'Успехът не приключва с първата продажба. Нашият двигател обхваща целия път на клиента, от откриването на бранда до момента, в който той става постоянен и ви препоръчва на други. Изключете някое ниво вдясно и вижте къде се прекъсва веригата.',
        'Всеки уеб елемент и структура са проектирани с една единствена цел — да насочат посетителя към обаждане, запитване или покупка. Дизайнът подчинява естетиката на бизнес конверсията.'
            => 'Всеки уеб елемент и структура са проектирани с една единствена цел, да насочат посетителя към обаждане, запитване или покупка. Дизайнът подчинява естетиката на бизнес конверсията.',
        'Партнираме си с:'
            => 'Партнираме си с',
        'Всяка страница води към едно действие: обаждане, запитване или покупка. Дизайнът е в услуга на това.'
            => 'Всяка страница води към едно действие. Това е обаждане, запитване или покупка. Дизайнът е в услуга на това.',
        'Изграждаме интелигентни имейл системи, които комуникират с клиентите ви в най-важните моменти – автоматично, без нужда от ваша ръчна намеса.'
            => 'Изграждаме интелигентни имейл системи, които комуникират с клиентите ви в най-важните моменти. Автоматично, без нужда от ваша ръчна намеса.',
        'В концепцията има форма за заявка за маса: гостът изпраща ден, час и брой хора, а вие потвърждавате по телефона. Ако използвате система за резервации, можем да сложим връзка към нея.'
            => 'В сайта има форма за заявка за маса. Гостът изпраща ден, час и брой хора, а вие потвърждавате по телефона. Ако използвате система за резервации, можем да сложим връзка към нея.',
        '„Колко струва?“, „Имате ли час в събота?“: десетки съобщения, на които отговаряте между две процедури.'
            => '„Колко струва?“, „Имате ли час в събота?“ Десетки съобщения, на които отговаряте между две процедури.',
        'В концепцията има форма за заявка: клиентката избира процедура и удобен ден, а вие потвърждавате. Ако използвате платформа за записване, слагаме връзка към нея.'
            => 'В сайта има форма за заявка. Клиентката избира процедура и удобен ден, а вие потвърждавате. Ако използвате платформа за записване, слагаме връзка към нея.',
        'Смяна на масло, гуми, годишен преглед: идват при първия, който им напомни.'
            => 'Смяна на масло, гуми, годишен преглед. Клиентите идват при първия, който им напомни.',
        'Диагностика, смяна на масло, гуми: поне „от … €“.'
            => 'Диагностика, смяна на масло, гуми, поне с цена „от … €“.',
        'По желание: автоматично напомняне за смяна на масло или за сезонните гуми.'
            => 'По желание и автоматично напомняне за смяна на масло или за сезонните гуми.',
        'В концепцията има форма за запитване: клиентът изпраща колата и проблема, а вие му се обаждате с удобен час.'
            => 'В сайта има форма за запитване. Клиентът изпраща колата и проблема, а вие му се обаждате с удобен час.',
        'Да, това е отделната ни услуга „Имейл кампании“: напомняния за масло, гуми и годишен преглед до клиентите, които са се съгласили.'
            => 'Да, това е отделната ни услуга „Имейл кампании“. Изпращаме напомняния за масло, гуми и годишен преглед до клиентите, които са се съгласили.',
        'Оглед, оферта, срок, работа, предаване: клиентът знае какво го чака.'
            => 'Оглед, оферта, срок, работа, предаване. Клиентът знае какво го чака.',
        'Всяка поръчка е разговор: „Колко струва?“, „Имате ли?“, „Как да платя?“.'
            => 'Всяка поръчка е разговор. „Колко струва?“, „Имате ли?“, „Как да платя?“',
        'Защото магазинът зависи от много неща: продукти, плащания, доставки. Казвате ни какво ви трябва и получавате точна оферта.'
            => 'Защото магазинът зависи от много неща, като продукти, плащания и доставки. Казвате ни какво ви трябва и получавате точна оферта.',
        'Три начина да работим заедно: от професионално онлайн присъствие до външен маркетингов отдел на вашата компания.'
            => 'Три начина да работим заедно, от професионално онлайн присъствие до външен маркетингов отдел на вашата компания.',
        'Годишно: 2 месеца безплатно'
            => 'Годишно, 2 месеца безплатно',
        'Одобрение преди пускане: виждате сайта и го одобрявате, преди да тръгне'
            => 'Одобрение преди пускане. Виждате сайта и го одобрявате, преди да тръгне',
        'Плащане на две части: депозит преди офертата и демото, остатъкът при съгласие'
            => 'Плащане на две части. Депозит преди офертата и демото, остатъкът при съгласие',
        '€400 старт: изграждаме всичко и първия месец'
            => '€400 еднократно за изграждането и първия месец',
        '€800 старт: изграждаме или поправяме сегашния ви сайт и първия месец'
            => '€800 еднократно за нов или обновен сайт и първия месец',
        'Какво включва: всичко от Стартов двигател, плюс'
            => 'Включва всичко от Стартов двигател, плюс',
        'Какво включва: според нуждите ви, всичко от Активен маркетинг и растеж, плюс'
            => 'Включва според нуждите ви всичко от Активен маркетинг и растеж, плюс',
        'С вашето съгласие плащате останалата част от старта. Стандартният срок е 1–2 седмици, а сайтът тръгва едва след вашето одобрение.'
            => 'С вашето съгласие плащате останалата част от старта. Стандартният срок е от 1 до 2 седмици, а сайтът тръгва едва след вашето одобрение.',
        'За Стартов двигател: €100 при заявка за оферта и демо (не се връщат), останалите €300 при съгласие. За Активен маркетинг и растеж: €200 при заявка за оферта и демо (не се връщат), останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.'
            => 'За Стартов двигател плащате €100 при заявка за оферта и демо (не се връщат) и останалите €300 при съгласие. За Активен маркетинг и растеж плащате €200 при заявка за оферта и демо (не се връщат) и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.',
        'Годишно плащане: 2 месеца безплатно'
            => 'Годишно плащане с 2 месеца безплатно',
        'Абонамент: цена за 6 месеца'
            => 'Фиксирана абонаментна цена за 6 месеца',
        'Стандартно 1–2 седмици за изграждане. Уточняваме срока в началото и го спазваме.'
            => 'Стандартно от 1 до 2 седмици за изграждане. Уточняваме срока в началото и го спазваме.',
        'Гаранция: връщаме парите'
            => 'Гаранция за връщане на парите',
        'Programming doesn\'t stop at work: I study software engineering at the University of Veliko Tarnovo. With you, though, I speak without technical words. I tell you what we did and why it matters for your business.'
            => 'Programming doesn\'t stop at work. I study software engineering at the University of Veliko Tarnovo. With you, though, I speak without technical words. I tell you what we did and why it matters for your business.',
        'My work starts with how your customers search for you. I set up your Google profile, write the texts, run ads on Google and Meta, and send the emails. I also check whether assistants like ChatGPT find you. In the end I check what matters most: are calls and enquiries coming in?'
            => 'My work starts with how your customers search for you. I set up your Google profile, write the texts, run ads on Google and Meta, and send the emails. I also check whether assistants like ChatGPT find you. In the end I check what matters most, whether calls and enquiries are coming in.',
        'To bring real results, a website needs constant care and development, not just to sit there as a business card. The monthly subscription covers round-the-clock technical security, hosting, regular backups and updates, as well as the marketing work: tracking visits in Google Analytics, looking after your Google Maps profile and making sure AI assistants (ChatGPT, Gemini) understand you, email communication and ongoing changes to the site.'
            => 'To bring real results, a website needs constant care and development, not just to sit there as a business card. The monthly subscription covers round-the-clock technical security, hosting, regular backups and updates, as well as the marketing work. That means tracking visits in Google Analytics, looking after your Google Maps profile and making sure AI assistants (ChatGPT, Gemini) understand you, email communication and ongoing changes to the site.',
        'The start price is paid in two parts. For Starter Engine: €100 when you request a quote and demo and the remaining €300 when you agree. For Active Marketing and Growth: €200 when you request a quote and demo and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue. Custom Marketing also has a deposit before the quote and the demo: the amount is set in the custom quote and is not refunded either. The subscription starts in the second month.'
            => 'The start price is paid in two parts. For Starter Engine you pay €100 when you request a quote and demo and the remaining €300 when you agree. For Active Marketing and Growth you pay €200 when you request a quote and demo and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue. Custom Marketing also has a deposit before the quote and the demo. The amount is set in the custom quote and is not refunded either. The subscription starts in the second month.',
        'When the website and the marketing go to different contractors, communication often slows down and things do not line up – the marketing strategy runs into technical limits of the platform, and the website lacks tracking codes and the right structure. When they are designed together, the website is built with analytics, email funnels and SEO architecture in place from the very start.'
            => 'When the website and the marketing go to different contractors, communication often slows down and things do not line up. The marketing strategy runs into technical limits of the platform, and the website lacks tracking codes and the right structure. When they are designed together, the website is built with analytics, email funnels and SEO architecture in place from the very start.',
        'Yes. Websites come with an easy-to-use admin panel. On top of that, your subscription includes our regular support – so when you need to change prices, offers or texts, we can quickly do it for you.'
            => 'Yes. Websites come with an easy-to-use admin panel. On top of that, your subscription includes our regular support. So when you need to change prices, offers or texts, we can quickly do it for you.',
        'We saw it again and again. A business pays one person for the website and another for ads. Each does their part, but nobody answers the simple question: are customers coming?'
            => 'We saw it again and again. A business pays one person for the website and another for ads. Each does their part, but nobody answers the simple question of whether customers are coming.',
        'So we work as one team. Borislav builds the website and the technical side, Vladimir makes sure people find you and contact you. We answer for the whole path: from the first visit to the first enquiry, and after.'
            => 'So we work as one team. Borislav builds the website and the technical side, Vladimir makes sure people find you and contact you. We answer for the whole path, from the first visit to the first enquiry, and after.',
        'A customer sees your brand as one whole – from the first encounter online to the final deal. That is why we develop your digital strategy in an integrated way and connect design, technology and marketing into a single mechanism.'
            => 'A customer sees your brand as one whole, from the first encounter online to the final deal. That is why we develop your digital strategy in an integrated way and connect design, technology and marketing into a single mechanism.',
        'Speed is part of trust: the customer does not wait, does not doubt and reaches you.'
            => 'Speed is part of trust. The customer does not wait, does not doubt and reaches you.',
        "We take on the whole process: from coding the site and connecting the forms to your email or CRM to setting up the ads in full.\n\nWe are responsible for the whole system, from the visitor's first click to the first enquiry."
            => "We take on the whole process, from coding the site and connecting the forms to your email or CRM to setting up the ads in full.\n\nWe are responsible for the whole system, from the visitor's first click to the first enquiry.",
        'ad tracking is set up: you measure every enquiry and sale'
            => 'ad tracking is set up, so you measure every enquiry and sale',
        'Success does not end with the first sale. Our engine covers the customer\'s whole journey – from discovering the brand to the moment they become a regular and recommend you to others. Switch off a level and see where the chain breaks.'
            => 'Success does not end with the first sale. Our engine covers the customer\'s whole journey, from discovering the brand to the moment they become a regular and recommend you to others. Switch off a level and see where the chain breaks.',
        'Every web element and structure is designed with one single purpose – to guide the visitor to a call, an enquiry or a purchase. The design subordinates aesthetics to business conversion.'
            => 'Every web element and structure is designed with one single purpose, to guide the visitor to a call, an enquiry or a purchase. The design subordinates aesthetics to business conversion.',
        'We partner with:'
            => 'We partner with',
        'Every page leads to one action: a call, an inquiry or a purchase. The design serves that.'
            => 'Every page leads to one action. That is a call, an inquiry or a purchase. The design serves that.',
        'We build smart email systems that talk to your customers at the moments that matter – automatically, without manual work from you.'
            => 'We build smart email systems that talk to your customers at the moments that matter. Automatically, without manual work from you.',
        'Three ways to work together: from a professional online presence to an outside marketing department for your company.'
            => 'Three ways to work together, from a professional online presence to an outside marketing department for your company.',
        'Yearly: 2 months free'
            => 'Yearly, 2 months free',
        'Approval before launch: you see the website and approve it before it goes live'
            => 'Approval before launch. You see the website and approve it before it goes live',
        'Payment in two parts: a deposit before the quote and the demo, the rest when you agree'
            => 'Payment in two parts. A deposit before the quote and the demo, the rest when you agree',
        '€400 start: we build everything and the first month'
            => '€400 one-off for the build and the first month',
        '€800 start: we build or fix your current website and the first month'
            => '€800 one-off for a new or refreshed website and the first month',
        'What it includes: everything in Starter Engine, plus'
            => 'Includes everything in Starter Engine, plus',
        'What it includes: tailored to your needs, everything in Active Marketing and Growth, plus'
            => 'Includes, tailored to your needs, everything in Active Marketing and Growth, plus',
        'When you agree, you pay the rest of the start price. The standard time is 1–2 weeks, and the website goes live only after you approve it.'
            => 'When you agree, you pay the rest of the start price. The standard time is 1 to 2 weeks, and the website goes live only after you approve it.',
        'For Starter Engine: €100 when you request a quote and demo (non-refundable), the remaining €300 when you agree. For Active Marketing and Growth: €200 when you request a quote and demo (non-refundable), the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue.'
            => 'For Starter Engine you pay €100 when you request a quote and demo (non-refundable) and the remaining €300 when you agree. For Active Marketing and Growth you pay €200 when you request a quote and demo (non-refundable) and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue.',
        'Yearly billing: 2 months free'
            => 'Yearly billing with 2 months free',
        'Subscription: price for 6 months'
            => 'Fixed subscription price for 6 months',
        'Normally 1–2 weeks to build. We agree the deadline at the start and keep to it.'
            => 'Normally 1 to 2 weeks to build. We agree the deadline at the start and keep to it.',
        'Guarantee: we refund you'
            => 'Money-back guarantee',
    ];

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::whereIn('key', self::KEYS)->get() as $block) {
            $value = self::transform((array) $block->value);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => $block->key . self::BACKUP, 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $block->update(['value' => $value]);
            $changed[] = $block->key . '/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }

    /** Сменя само низове, които са точно стар текст. */
    public static function transform(array $value): array
    {
        array_walk_recursive($value, function (&$item) {
            if (is_string($item) && isset(self::REPLACE[$item])) {
                $item = self::REPLACE[$item];
            }
        });

        return $value;
    }
}
