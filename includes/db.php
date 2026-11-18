<?php
/**
 * SQLite connection + schema bootstrap.
 * Creates data/cms.sqlite on first run with all required tables.
 */

function cms_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbPath = __DIR__ . '/../data/cms.sqlite';
    $isNew = !file_exists($dbPath);

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    if ($isNew) {
        cms_bootstrap_schema($pdo);
    } else {
        cms_migrate_schema($pdo);
    }

    return $pdo;
}

function cms_bootstrap_schema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE content_overrides (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lang TEXT NOT NULL,
            path TEXT NOT NULL,
            value TEXT NOT NULL,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(lang, path)
        );

        CREATE TABLE blog_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            cover_image TEXT,
            published INTEGER NOT NULL DEFAULT 0,
            published_at TEXT,
            views INTEGER NOT NULL DEFAULT 0,
            telegram_announced INTEGER NOT NULL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE blog_post_i18n (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL REFERENCES blog_posts(id) ON DELETE CASCADE,
            lang TEXT NOT NULL,
            title TEXT NOT NULL DEFAULT '',
            excerpt TEXT NOT NULL DEFAULT '',
            body TEXT NOT NULL DEFAULT '',
            UNIQUE(post_id, lang)
        );

        CREATE TABLE login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            attempted_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX idx_content_overrides_lang ON content_overrides(lang);
        CREATE INDEX idx_blog_post_i18n_post ON blog_post_i18n(post_id);
        CREATE INDEX idx_login_attempts_ip_time ON login_attempts(ip, attempted_at);
    ");

    cms_seed_initial_blog_posts($pdo);
}

/**
 * The 3 starter articles' content (RU/EN/UZ), about corrugated packaging
 * production, with simple bold / italic / "> quote" formatting applied
 * (see includes/blog_helpers.php for the renderer). Shared by
 * cms_seed_initial_blog_posts() (insert-if-missing, used on a fresh DB)
 * and cms_upgrade_starter_blog_content() (explicit update of these exact
 * 3 articles' text, triggered from the admin panel).
 */
function cms_starter_blog_posts(): array {
    return [
        [
            'slug' => 'kak-proizvodyat-gofrokarton',
            'cover_image' => 'assets/images/blog/kak-proizvodyat-gofrokarton.webp',
            'published_at' => '2026-11-05 10:00:00',
            'i18n' => [
                'ru' => [
                    'title' => 'Как производят гофрокартон: от бумаги до прочной упаковки',
                    'excerpt' => 'Коротко о том, как на производстве превращают обычную бумагу в прочный гофрокартон и готовые коробки — через гофроагрегат, печать и высечку.',
                    'body' => <<<'EOT'
Гофрокартон кажется простым материалом, но путь от рулона бумаги до готовой коробки с логотипом заказчика проходит через **пять ключевых этапов производства**.

**Сырьё.** В основе гофрокартона — крафт-бумага (лайнер) для внешних и внутренних слоёв и более лёгкая бумага (флютинг) для волнистого среднего слоя. От плотности и качества этой бумаги *напрямую* зависит прочность будущей упаковки.

**Гофрирование.** На гофроагрегате флютинг пропускают между нагретыми валами с зубчатым профилем — бумага приобретает волнообразную форму. Затем волнистый слой склеивают с одним или двумя плоскими слоями крахмальным клеем под давлением и температурой — так получается гофрополотно. Профиль гофры (**A, B, C, E**) определяет высоту волны и, соответственно, амортизирующие свойства и толщину итогового картона.

**Флексопечать.** Если на упаковке должен быть логотип или фирменный дизайн, гофрополотно проходит через флексографскую печатную машину — гибкие полимерные клише наносят краску на поверхность, за один проход можно нанести от одного до четырёх цветов.

**Высечка и склейка.** Специальный высекальный пресс вырубает заготовку нужной формы и размера по заранее подготовленному штампу — именно на этом этапе формируются клапаны, отверстия для ручек и линии биговки, по которым коробка будет складываться. После высечки заготовку складывают и склеивают или сшивают скобой по шву — и получается готовый плоский короб, удобный для хранения и перевозки до сборки у клиента.

**Контроль качества.** На каждом этапе — от входного контроля сырья до проверки готовой партии — проверяются плотность, прочность на сжатие и точность размеров, чтобы упаковка выдержала транспортировку и штабелирование на складе.

> «Самая частая ошибка заказчиков — выбирать упаковку только по цене, не глядя на профиль гофры и плотность картона. В итоге экономия на материале оборачивается потерями на возвратах и повреждённом товаре». — **технолог производства INTER PACK**

Именно поэтому на производстве INTER PACK мы контролируем *каждый* из этих этапов — от рулона бумаги до паллеты с готовыми коробками.
EOT,
                ],
                'en' => [
                    'title' => 'How corrugated cardboard is made: from paper to sturdy packaging',
                    'excerpt' => 'A short look at how plain paper becomes strong corrugated board and finished boxes — through the corrugator, printing and die-cutting.',
                    'body' => <<<'EOT'
Corrugated board looks simple, but the path from a roll of paper to a finished, branded box goes through **five key production stages**.

**Raw material.** Corrugated board starts with kraft linerboard for the outer and inner layers, and a lighter paper (fluting medium) for the wavy middle layer. The strength of the finished packaging depends *directly* on the density and quality of this paper.

**Corrugating.** On the corrugating machine, the fluting medium passes between heated, toothed rollers that shape it into a wave. The wavy layer is then glued to one or two flat liners under heat and pressure using starch-based adhesive, producing corrugated board. The flute profile (**A, B, C, E**) determines the wave height and, with it, the cushioning properties and overall thickness of the board.

**Flexographic printing.** If the packaging needs a logo or branded design, the board passes through a flexo printing press, where flexible polymer plates apply ink to the surface — one to four colors in a single pass.

**Die-cutting and gluing.** A die-cutting press cuts the blank to the required shape and size using a pre-made die — this is where the flaps, handle holes and crease lines are formed. After cutting, the blank is folded and glued or stitched along the seam, producing a flat box that's easy to store and ship before final assembly at the customer's site.

**Quality control.** At every stage — from incoming raw material checks to the finished batch — density, compression strength and dimensional accuracy are verified, so the packaging survives transport and warehouse stacking.

> "The most common mistake customers make is choosing packaging by price alone, without looking at the flute profile or board density. Saving on material ends up costing far more in returns and damaged goods." — **INTER PACK production engineer**

That's why at INTER PACK we control *every one* of these stages — from the paper roll to the pallet of finished boxes.
EOT,
                ],
                'uz' => [
                    'title' => "Gofrokarton qanday ishlab chiqariladi: qog'ozdan mustahkam qadoqgacha",
                    'excerpt' => "Oddiy qog'oz qanday qilib mustahkam gofrokarton va tayyor qutilarga aylanishi haqida qisqacha — gofroagregat, bosma va shtamplashgacha.",
                    'body' => <<<'EOT'
Gofrokarton sodda material bo'lib ko'rinadi, ammo qog'oz rulonidan mijoz logotipi bilan tayyor qutigacha bo'lgan yo'l **beshta asosiy ishlab chiqarish bosqichidan** o'tadi.

**Xomashyo.** Gofrokarton asosida tashqi va ichki qatlamlar uchun kraft-qog'oz (layner) va to'lqinsimon o'rta qatlam uchun yengilroq qog'oz (flyuting) yotadi. Tayyor qadoqning mustahkamligi *aynan* shu qog'ozning zichligi va sifatiga bog'liq.

**Gofrlash.** Gofroagregatda flyuting tishli profilli qizdirilgan valiklar orasidan o'tkaziladi — qog'oz to'lqinsimon shaklga ega bo'ladi. So'ngra to'lqinsimon qatlam bosim va issiqlik ostida kraxmal yelimi yordamida bir yoki ikki tekis qatlam bilan yopishtiriladi — natijada gofropolotno hosil bo'ladi. Gofra profili (**A, B, C, E**) to'lqin balandligini va shu bilan birga amortizatsiya xususiyatlarini hamda tayyor kartonning qalinligini belgilaydi.

**Fleksobosma.** Agar qadoqda logotip yoki firma dizayni bo'lishi kerak bo'lsa, gofropolotno fleksografik bosma mashinasidan o'tkaziladi — moslashuvchan polimer klishelar sirtga bo'yoq tushiradi, bir yo'lda bitta yo'ldan to'rttagacha rang qo'llash mumkin.

**Shtamplash va yelimlash.** Maxsus shtamplash pressi oldindan tayyorlangan shtamp bo'yicha zagotovkani kerakli shakl va o'lchamda kesib oladi — aynan shu bosqichda klapanlar, dastak teshiklari va bukilish chiziqlari hosil bo'ladi. Kesishdan so'ng zagotovka buklanadi va chok bo'ylab yelimlanadi yoki skrepka bilan birlashtiriladi — natijada mijozda yig'ilishigacha saqlash va tashishga qulay tekis tayyor quti hosil bo'ladi.

**Sifat nazorati.** Har bir bosqichda — xomashyoni qabul qilishdan tayyor partiyani tekshirishgacha — zichlik, siqilishga chidamlilik va o'lchamlar aniqligi tekshiriladi, shunda qadoq tashish va omborda shtabellashga bardosh beradi.

> «Mijozlarning eng ko'p uchraydigan xatosi — qadoqni faqat narxiga qarab tanlash, gofra profili va karton zichligiga e'tibor bermasdan. Natijada materialda tejamkorlik keyinchalik qaytarishlar va shikastlangan tovarlarda yo'qotishga aylanadi». — **INTER PACK ishlab chiqarish texnologi**

Shuning uchun INTER PACK ishlab chiqarishida biz ushbu bosqichlarning *har birini* — qog'oz rulonidan tayyor qutilar paletigacha — nazorat qilamiz.
EOT,
                ],
            ],
        ],
        [
            'slug' => 'profili-gofry-a-b-c-e',
            'cover_image' => 'assets/images/blog/profili-gofry-a-b-c-e.webp',
            'published_at' => '2026-11-10 10:00:00',
            'i18n' => [
                'ru' => [
                    'title' => 'Профили гофры A, B, C и E: в чём разница и какой выбрать',
                    'excerpt' => 'Разбираем, чем отличаются профили гофры A, B, C и E, и как выбрать подходящий именно под ваш товар — чтобы не переплатить и не получить повреждённую посылку.',
                    'body' => <<<'EOT'
Когда речь заходит о коробке «под заказ», один из первых вопросов — какой профиль гофры нужен. Профиль — это высота и частота волны среднего слоя картона, и от него зависит и прочность, и толщина стенки, и даже внешний вид коробки.

**Профиль A** — самая высокая волна (около 4,5–5 мм), лучше всего амортизирует удары и поглощает вибрацию при перевозке. Подходит для хрупких и тяжёлых товаров — бытовой техники, стекла, керамики.

**Профиль B** — волна ниже (около 2,5–3 мм) и чаще расположена, поэтому стенка тоньше, но более устойчива к продавливанию сверху. Хорошо работает там, где важна плоская ровная поверхность под печать — например, для консервов и штучных товаров на полке.

**Профиль C** — промежуточный вариант (около 3,5–4 мм), сочетает амортизацию профиля A и устойчивость к сжатию профиля B. Это *самый универсальный* и часто используемый профиль для транспортной тары и обычных коробок.

**Профиль E** (микрогофра) — самая тонкая волна (около 1,5 мм), даёт гладкую поверхность для качественной печати и подходит там, где важна компактность — потребительская упаковка, коробки для пиццы, подарочная упаковка.

Также используются многослойные комбинации — например, двухслойный гофрокартон **BC** объединяет свойства профилей B и C и применяется для особо тяжёлых и ответственных грузов.

> «Клиенты часто просят самый прочный картон "на всякий случай" — но это почти всегда переплата. Профиль C закрывает 80% задач, а под реально тяжёлые грузы мы предлагаем BC, а не завышаем всё подряд до профиля A». — **менеджер по работе с клиентами INTER PACK**

Как выбрать? Отталкивайтесь от трёх вопросов: сколько весит товар и как хрупок, как долго и в каких условиях он будет храниться и перевозиться, и важна ли яркая печать на упаковке. Если сомневаетесь — наши менеджеры *всегда* помогут подобрать профиль под конкретную задачу, а не продать «что есть».
EOT,
                ],
                'en' => [
                    'title' => "Flute profiles A, B, C and E: what's the difference and which to choose",
                    'excerpt' => 'A breakdown of flute profiles A, B, C and E, and how to pick the right one for your product — without overpaying or risking a damaged shipment.',
                    'body' => <<<'EOT'
When ordering a custom box, one of the first questions is which flute profile you need. The profile is the height and frequency of the wave in the board's middle layer, and it affects strength, wall thickness and even the box's appearance.

**A-flute** — the tallest wave (about 4.5–5 mm), offering the best cushioning against shocks and vibration during transport. Suited to fragile or heavy items — appliances, glass, ceramics.

**B-flute** — a lower, more frequent wave (about 2.5–3 mm), giving a thinner but more crush-resistant wall. Works well where a flat, even surface for printing matters — canned goods and shelf-ready retail items.

**C-flute** — a middle ground (about 3.5–4 mm) combining A-flute's cushioning with B-flute's crush resistance. It's the *most versatile* and commonly used profile for transport boxes.

**E-flute** (microflute) — the thinnest wave (about 1.5 mm), giving a smooth surface for high-quality printing and a compact profile — ideal for retail packaging, pizza boxes and gift packaging.

Multi-layer combinations are also used — double-wall **BC** board, for example, combines the properties of B and C flutes for especially heavy or critical loads.

> "Customers often ask for the strongest board 'just in case' — but that's almost always overpaying. C-flute covers 80% of needs, and for genuinely heavy loads we recommend BC rather than defaulting everyone to A-flute." — **INTER PACK account manager**

How to choose? Start with three questions: how heavy and fragile is the product, how long and under what conditions will it be stored and shipped, and does the packaging need high-quality printed branding. If in doubt, our team will *always* help you pick the right profile for your specific case — not just sell you what's in stock.
EOT,
                ],
                'uz' => [
                    'title' => "A, B, C va E gofra profillari: farqi nimada va qaysi birini tanlash kerak",
                    'excerpt' => "A, B, C va E gofra profillari o'rtasidagi farqni va mahsulotingiz uchun to'g'ri profilni qanday tanlashni — ortiqcha to'lamasdan va yuk shikastlanish xavfisiz — ko'rib chiqamiz.",
                    'body' => <<<'EOT'
Buyurtma asosida quti haqida gap ketganda, birinchi savollardan biri — qanday gofra profili kerakligi. Profil — kartonning o'rta qatlamidagi to'lqin balandligi va chastotasi bo'lib, undan mustahkamlik, devor qalinligi va hatto qutining tashqi ko'rinishi bog'liq.

**A profili** — eng baland to'lqin (taxminan 4,5–5 mm), tashish paytida zarba va tebranishni eng yaxshi yutadi. Mo'rt va og'ir tovarlar uchun mos — maishiy texnika, shisha, keramika.

**B profili** — pastroq va tez-tez joylashgan to'lqin (taxminan 2,5–3 mm), shuning uchun devor yupqaroq, lekin yuqoridan bosilishga chidamliroq. Bosma uchun tekis yuza muhim bo'lgan joylarda yaxshi ishlaydi — konservalar va peshtaxta tovarlari uchun.

**C profili** — oraliq variant (taxminan 3,5–4 mm), A profilining amortizatsiyasi va B profilining siqilishga chidamliligini birlashtiradi. Bu transport tarasi va oddiy qutilar uchun *eng universal* va ko'p qo'llaniladigan profil.

**E profili** (mikrogofra) — eng yupqa to'lqin (taxminan 1,5 mm), sifatli bosma uchun silliq sirt beradi va ixchamlik muhim bo'lgan joylarda qo'llaniladi — iste'mol qadog'i, pitsa qutilari, sovg'a qadog'i.

Shuningdek ko'p qatlamli kombinatsiyalar ham qo'llaniladi — masalan, ikki qatlamli **BC** gofrokarton B va C profillarining xususiyatlarini birlashtiradi va alohida og'ir hamda mas'uliyatli yuklar uchun qo'llaniladi.

> «Mijozlar ko'pincha "ehtiyot shart" deb eng mustahkam kartonni so'rashadi — lekin bu deyarli har doim ortiqcha to'lov. C profili ehtiyojlarning 80% ini qoplaydi, haqiqatan og'ir yuklar uchun esa hammasini A profiliga oshirish o'rniga BC ni taklif qilamiz». — **INTER PACK mijozlar bilan ishlash menejeri**

Qanday tanlash kerak? Uchta savoldan kelib chiqing: tovar qancha og'ir va qanchalik mo'rt, u qancha vaqt va qanday sharoitda saqlanadi hamda tashiladi, va qadoqda sifatli bosma muhimmi. Agar ishonchingiz komil bo'lmasa — bizning menejerlarimiz *har doim* aniq vazifangizga mos profilni tanlashda yordam beradi, "nima bor bo'lsa shuni" sotish o'rniga.
EOT,
                ],
            ],
        ],
        [
            'slug' => '5-oshibok-pri-vybore-upakovki',
            'cover_image' => 'assets/images/blog/5-oshibok-pri-vybore-upakovki.webp',
            'published_at' => '2026-11-15 10:00:00',
            'i18n' => [
                'ru' => [
                    'title' => '5 ошибок при выборе упаковки, которые дорого обходятся бизнесу',
                    'excerpt' => 'Неправильно подобранная упаковка — это не мелочь, а потери на возвратах, рекламациях и лишней логистике. Разбираем самые частые ошибки.',
                    'body' => <<<'EOT'
**1. Коробка «на глаз», без расчёта под товар.** Слишком большая коробка — это лишнее место в фуре и более высокая стоимость доставки по объёмному весу; слишком маленькая — риск, что товар не влезет или помнётся при упаковке. Размер короба стоит считать под конкретный товар, а не брать «стандартный, который был».

**2. Экономия на плотности картона.** Более тонкий и дешёвый картон кажется выгодным решением — пока партия не приходит клиенту с мятыми углами и вскрытыми швами. Разница в стоимости картона обычно *в разы* меньше, чем стоимость испорченного товара и возврата.

**3. Неправильный профиль гофры под нагрузку.** Лёгкий товар в слишком прочной (и дорогой) таре — переплата; тяжёлый товар в тонкой гофре — риск деформации при штабелировании на складе или в машине. Профиль гофры должен соответствовать весу и условиям хранения.

**4. Отсутствие фирменной печати.** Безликая коричневая коробка — упущенная возможность: с флексопечатью та же самая упаковка превращается в инструмент узнаваемости бренда и может повысить доверие клиента *ещё до* вскрытия посылки.

**5. Игнорирование условий хранения и влажности.** Гофрокартон теряет прочность при повышенной влажности. Если товар хранится на складе без климат-контроля или перевозится в сезон дождей, стоит заранее обсудить с производителем влагостойкое покрытие или более прочный профиль.

> «Больше половины рекламаций, с которыми к нам приходят, связаны не с браком упаковки, а с тем, что её изначально подобрали неправильно под задачу. Пять минут на консультацию с менеджером экономят потом тысячи долларов на возвратах». — **руководитель отдела продаж INTER PACK**

Правильно подобранная упаковка окупается за счёт меньшего процента повреждённых товаров и возвратов — и обходится в итоге **дешевле**, чем кажется на первый взгляд.
EOT,
                ],
                'en' => [
                    'title' => '5 costly packaging mistakes businesses make',
                    'excerpt' => "The wrong packaging isn't a minor detail — it shows up as returns, claims and wasted logistics costs. Here are the most common mistakes.",
                    'body' => <<<'EOT'
**1. Sizing the box "by eye," without calculating it for the product.** A box that's too large wastes truck space and raises dimensional-weight shipping costs; one that's too small risks the product not fitting or getting crushed during packing. Box size should be calculated for the specific product, not taken from "whatever standard size we have."

**2. Cutting corners on board thickness.** Thinner, cheaper board looks like a good deal — until the shipment arrives with crushed corners and split seams. The savings on cardboard are usually *far smaller* than the cost of damaged goods and returns.

**3. The wrong flute profile for the load.** A light product in packaging that's too strong (and expensive) means overpaying; a heavy product in thin board risks deforming under stacking in a warehouse or truck. The flute profile should match the product's weight and storage conditions.

**4. No branded printing.** A plain brown box is a missed opportunity — with flexo printing, the same packaging becomes a brand-recognition tool and can build customer trust *before* the package is even opened.

**5. Ignoring storage conditions and humidity.** Corrugated board loses strength in high humidity. If the product is stored in a warehouse without climate control, or shipped during a rainy season, it's worth discussing a moisture-resistant coating or a stronger profile with the manufacturer in advance.

> "More than half of the complaints we see aren't about defective packaging — they're about packaging that was the wrong choice from the start. A five-minute consultation with our team saves thousands later in returns." — **INTER PACK head of sales**

The right packaging pays for itself through fewer damaged shipments and returns — and usually costs **less** in the long run than it first appears.
EOT,
                ],
                'uz' => [
                    'title' => "Biznesga qimmatga tushadigan qadoqlash tanlovidagi 5 ta xato",
                    'excerpt' => "Noto'g'ri tanlangan qadoq — mayda masala emas, balki qaytarishlar, pretenziyalar va ortiqcha logistika xarajatlari. Eng ko'p uchraydigan xatolarni ko'rib chiqamiz.",
                    'body' => <<<'EOT'
**1. Quti «ko'z bilan», tovarga hisoblamasdan.** Juda katta quti — mashinada ortiqcha joy va hajm bo'yicha yuqoriroq yetkazib berish narxi; juda kichik quti — tovar sig'may qolishi yoki qadoqlashda ezilish xavfi. Quti o'lchamini "bor standart"dan emas, aniq tovarga qarab hisoblash kerak.

**2. Karton zichligida tejash.** Yupqaroq va arzonroq karton foydali yechim bo'lib ko'rinadi — mijozga partiya ezilgan burchaklar va ochilib ketgan choklar bilan kelguncha. Karton narxidagi farq odatda buzilgan tovar va qaytarish xarajatidan *ancha* kam bo'ladi.

**3. Yukka mos kelmaydigan gofra profili.** Yengil tovar juda mustahkam (va qimmat) taraga solinishi — ortiqcha to'lov; og'ir tovar yupqa gofrada esa omborda yoki mashinada shtabellashda deformatsiya xavfi. Gofra profili tovar og'irligi va saqlash sharoitiga mos bo'lishi kerak.

**4. Firma bosmasining yo'qligi.** Oddiy jigarrang quti — boy berilgan imkoniyat: fleksobosma bilan xuddi shu qadoq brend tanilishi vositasiga aylanadi va pochta ochilmasdan *oldinoq* mijoz ishonchini oshirishi mumkin.

**5. Saqlash sharoiti va namlikka e'tiborsizlik.** Gofrokarton yuqori namlikda mustahkamligini yo'qotadi. Agar tovar iqlim nazoratisiz omborda saqlansa yoki yomg'irli mavsumda tashilsa, ishlab chiqaruvchi bilan oldindan namlikka chidamli qoplama yoki mustahkamroq profilni muhokama qilish kerak.

> «Bizga keladigan shikoyatlarning yarmidan ko'pi qadoq brakidan emas, balki uni vazifaga noto'g'ri tanlashdan kelib chiqadi. Menejer bilan besh daqiqalik maslahat keyinchalik minglab dollar qaytarish xarajatini tejaydi». — **INTER PACK savdo bo'limi boshlig'i**

To'g'ri tanlangan qadoq shikastlangan tovarlar va qaytarishlar foizi kamayishi hisobiga o'zini oqlaydi — va oxir-oqibatda birinchi qarashda ko'ringanidan **arzonroqqa** tushadi.
EOT,
                ],
            ],
        ],
    ];
}

function cms_seed_initial_blog_posts(PDO $pdo): int {
    $posts = cms_starter_blog_posts();

    $checkExists = $pdo->prepare('SELECT 1 FROM blog_posts WHERE slug = ?');
    $insertPost = $pdo->prepare('INSERT INTO blog_posts (slug, cover_image, published, published_at) VALUES (?, ?, 1, ?)');
    $insertI18n = $pdo->prepare('INSERT INTO blog_post_i18n (post_id, lang, title, excerpt, body) VALUES (?, ?, ?, ?, ?)');

    $added = 0;
    foreach ($posts as $post) {
        $checkExists->execute([$post['slug']]);
        if ($checkExists->fetch()) {
            continue; // already exists (earlier seed or manually created) — don't duplicate
        }
        $insertPost->execute([$post['slug'], $post['cover_image'], $post['published_at']]);
        $postId = (int) $pdo->lastInsertId();
        foreach ($post['i18n'] as $lang => $fields) {
            $insertI18n->execute([$postId, $lang, $fields['title'], $fields['excerpt'], $fields['body']]);
        }
        $added++;
    }
    return $added;
}

/**
 * Explicitly overwrites the title/excerpt/body of the 3 starter articles
 * (matched by slug) with the latest version of their content — used to push
 * a formatting/copy improvement to a database where they were seeded
 * earlier. Only touches these 3 known slugs; does nothing to any other
 * article. Returns how many of the 3 were found and updated.
 */
function cms_upgrade_starter_blog_content(PDO $pdo): int {
    $posts = cms_starter_blog_posts();

    $findId = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ?');
    $updateI18n = $pdo->prepare('
        UPDATE blog_post_i18n SET title = ?, excerpt = ?, body = ?
        WHERE post_id = ? AND lang = ?
    ');

    $updated = 0;
    foreach ($posts as $post) {
        $findId->execute([$post['slug']]);
        $postId = $findId->fetchColumn();
        if ($postId === false) {
            continue; // this starter article was deleted — leave it alone
        }
        foreach ($post['i18n'] as $lang => $fields) {
            $updateI18n->execute([$fields['title'], $fields['excerpt'], $fields['body'], $postId, $lang]);
        }
        $updated++;
    }
    return $updated;
}

/**
 * Lightweight forward-only migration: add tables/columns that might be
 * missing on an existing DB (safe to run every request; cheap no-ops).
 */
function cms_migrate_schema(PDO $pdo): void {
    $columns = $pdo->query('PRAGMA table_info(blog_posts)')->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('views', $columns, true)) {
        $pdo->exec('ALTER TABLE blog_posts ADD COLUMN views INTEGER NOT NULL DEFAULT 0');
    }
    if (!in_array('telegram_announced', $columns, true)) {
        $pdo->exec('ALTER TABLE blog_posts ADD COLUMN telegram_announced INTEGER NOT NULL DEFAULT 0');
    }
}
