<?php

/*
|--------------------------------------------------------------------------
| Landing Catalog — کاتالوگ مشترک لندینگ‌ها
|--------------------------------------------------------------------------
| دیتای مشترک بین محصولات PGL: لیگ‌ها (+ تیم‌ها با لوگو)، استادیوم‌ها، جام‌ها.
| قرارداد ترجمه: هر آرایه با کلیدهای دقیقاً fa و en یک رشته دوزبانه است.
|
| ⚠️ شماره فایل‌های لوگو (emb_XXXX_l.png) از پوشه emblemLc پچ کپی شده‌اند؛
| شماره‌گذاری با تحلیل تصویری تخمین زده شده — اصلاح نهایی با دیتابیس پچ
| فقط همین فایل است (فیلد logo هر تیم/لیگ).
*/

return [

    'leagues' => [

        'persian-gulf-pro-league' => [
            'name' => ['fa' => 'لیگ برتر خلیج فارس', 'en' => 'Persian Gulf Pro League'],
            'country' => ['fa' => 'ایران', 'en' => 'Iran'],
            'tag' => 'IRN',
            'level' => ['fa' => 'سطح ۱ ایران', 'en' => 'Iran · Tier 1'],
            'teams_count' => 18,
            'logo' => 'images/landings/logos/emb/emb_0001_l.png',
            'description' => [
                'fa' => 'کامل‌ترین بازسازی لیگ برتر خلیج فارس در تاریخ پچ‌های فارسی: هر ۱۸ تیم با کیت‌های رسمی، چهره‌های سه‌بعدی بازیکنان، استادیوم‌های اختصاصی، گزارش فارسی و اسکوربورد شبکه‌های ورزشی ایران. جدول لیگ، قانون بازیافت و ساختار فصل کاملاً منطبق بر واقعیت است.',
                'en' => 'The most complete rebuild of the Persian Gulf Pro League ever made in a Persian patch: all 18 clubs with authentic kits, 3D player faces, dedicated stadiums, Persian commentary and Iranian TV graphics — with a fully authentic league table and season structure.',
            ],
            'teams' => [
                ['name' => ['fa' => 'پرسپولیس', 'en' => 'Persepolis FC'],
                    'detail' => [
                        'stats' => [
                            ['label' => ['fa' => 'چهره واقعی بازیکن', 'en' => 'Real player faces'], 'value' => ['fa' => '۲۶ بازیکن', 'en' => '26 players']],
                            ['label' => ['fa' => 'استادیوم واقعی', 'en' => 'Real stadium'], 'value' => ['fa' => 'آزادی — دارد', 'en' => 'Azadi — Yes']],
                            ['label' => ['fa' => 'چهره مربی', 'en' => 'Coach face'], 'value' => ['fa' => 'دارد', 'en' => 'Yes']],
                        ],
                        'description' => [
                            'fa' => 'پرطرفدارترین تیم ایران با کامل‌ترین پوشش پچ: چهره‌های سه‌بعدی ۲۶ بازیکن اصلی و ذخیره، کیت‌های فصل با اسپانسر، ورودیه اختصاصی آزادی و ورق‌های تشویقی سکوها.',
                            'en' => 'The best-supported club in Iran with the fullest coverage: 3D faces for 26 players, sponsored kits, a dedicated Azadi entrance scene and crowd cards.',
                        ],
                        'tags' => [
                            ['fa' => 'گیم‌پلی ترکیب اصلی', 'en' => 'First-XI gameplay'],
                            ['fa' => 'ورودیه آزادی', 'en' => 'Azadi entrance'],
                            ['fa' => 'کیت‌های اسپانسردار', 'en' => 'Sponsored kits'],
                        ],
                        'kit3d' => [
                            'texture' => 'images/landings/kits/persepolis.png',
                            'accent' => '#C41622',
                            'secondary' => '#8E0F1A',
                            'caption' => ['fa' => 'کیت خانگی پرسپولیس — قرمز با شاش سفید', 'en' => 'Persepolis home kit — red with white sash'],

                        ],
'blocks' => [
                            [
                                'type' => 'video',
                                'title' => ['fa' => 'پیش‌نمایش ویدیویی', 'en' => 'Video preview'],
                                'url' => null,
                                'poster' => 'images/landings/shared/g2-gameplay.svg',
                                'caption' => ['fa' => 'تریلر پرسپولیس تو پچ — به‌زودی', 'en' => 'Persepolis in the mod — trailer coming soon'],
                            ],
                            [
                                'type' => 'bullets',
                                'title' => ['fa' => 'چرا پرسپولیس تو این پچ خاصه؟', 'en' => 'Why Persepolis feels special here'],
                                'items' => [
                                    ['icon' => 'crown', 'title' => ['fa' => 'کامل‌ترین پوشش چهره‌ها', 'en' => 'Fullest face coverage'], 'text' => ['fa' => '۲۶ بازیکن با سر سه‌بعدی اسکن‌شده و مینی‌فیس.', 'en' => '26 players with scanned 3D heads and minifaces.']],
                                    ['icon' => 'fire', 'title' => ['fa' => 'ورودیه آزادی', 'en' => 'Azadi entrance'], 'text' => ['fa' => 'دقیقاً همون حس شب‌های پرسپولیس.', 'en' => 'The exact feeling of Persepolis nights.']],
                                    ['icon' => 'star', 'title' => ['fa' => 'ستاره‌های تیم ملی', 'en' => 'National-team stars'], 'text' => ['fa' => 'چهره‌های واقعی ستاره‌های ملی داخل تیم.', 'en' => 'Real faces of the national stars in the squad.']],
                                ],
                            ],
                            [
                                'type' => 'kit3d',
                                'title' => ['fa' => 'کیت سه‌بعدی', 'en' => 'The kit in 3D'],
                                'texture' => 'images/landings/kits/persepolis.png',
                                'accent' => '#C41622',
                                'secondary' => '#8E0F1A',
                                'caption' => ['fa' => 'کیت خانگی پرسپولیس — قرمز با شاش سفید', 'en' => 'Persepolis home kit — red with white sash'],
                            ],
                            [
                                'type' => 'gallery',
                                'title' => ['fa' => 'از داخل بازی', 'en' => 'In-game'],
                                'images' => [
                                    ['src' => 'images/landings/shared/g2-gameplay.svg', 'caption' => ['fa' => 'گیم‌پلی', 'en' => 'Gameplay']],
                                    ['src' => 'images/landings/shared/g1-menu.svg', 'caption' => ['fa' => 'منو', 'en' => 'Menu']],
                                ],
                            ],
                        ],
                    ],
                    'logo' => null, 'city' => ['fa' => 'تهران', 'en' => 'Tehran']],
                ['name' => ['fa' => 'استقلال', 'en' => 'Esteghlal FC'],
                    'detail' => [
                        'stats' => [
                            ['label' => ['fa' => 'چهره واقعی بازیکن', 'en' => 'Real player faces'], 'value' => ['fa' => '۲۳ بازیکن', 'en' => '23 players']],
                            ['label' => ['fa' => 'استادیوم واقعی', 'en' => 'Real stadium'], 'value' => ['fa' => 'آزادی — دارد', 'en' => 'Azadi — Yes']],
                            ['label' => ['fa' => 'چهره مربی', 'en' => 'Coach face'], 'value' => ['fa' => 'دارد', 'en' => 'Yes']],
                        ],
                        'description' => [
                            'fa' => 'آبی‌پوشان پایتخت با چهره‌های واقعی ۲۳ بازیکن، کیت‌های آبی فصل جدید و مش‌های هواداری سکوی اختصاصی.',
                            'en' => 'The capital blues with 23 real faces, this-season kits and dedicated supporter meshes.',
                        ],
                        'tags' => [
                            ['fa' => 'گیم‌پلی ترکیب اصلی', 'en' => 'First-XI gameplay'],
                            ['fa' => 'کیت فصل جدید', 'en' => 'New-season kit'],
                        ],
                        'kit3d' => [
                            'texture' => 'images/landings/kits/esteghlal.png',
                            'accent' => '#3A7BD5',
                            'secondary' => '#17408B',
                            'caption' => ['fa' => 'کیت خانگی استقلال — آبی راه‌راه', 'en' => 'Esteghlal home kit — blue hoops'],
                        ],
                    ],
                    'logo' => 'emb_0112_l.png', 'city' => ['fa' => 'تهران', 'en' => 'Tehran']],
                ['name' => ['fa' => 'سپاهان', 'en' => 'Sepahan FC'],
                    'detail' => [
                        'stats' => [
                            ['label' => ['fa' => 'چهره واقعی بازیکن', 'en' => 'Real player faces'], 'value' => ['fa' => '۱۹ بازیکن', 'en' => '19 players']],
                            ['label' => ['fa' => 'استادیوم واقعی', 'en' => 'Real stadium'], 'value' => ['fa' => 'نقش جهان — دارد', 'en' => 'Naqsh-e Jahan — Yes']],
                            ['label' => ['fa' => 'چهره مربی', 'en' => 'Coach face'], 'value' => ['fa' => 'دارد', 'en' => 'Yes']],
                        ],
                        'description' => [
                            'fa' => 'زردهای اصفهان با استادیوم نقش جهان بازسازی‌شده، چهره‌های واقعی و کیت‌های زرد-سیاه فصل.',
                            'en' => 'The yellows of Isfahan with the rebuilt Naqsh-e Jahan stadium, real faces and yellow-black kits.',
                        ],
                        'tags' => [
                            ['fa' => 'استادیوم نقش جهان', 'en' => 'Naqsh-e Jahan'],
                            ['fa' => 'کیت زرد-سیاه', 'en' => 'Yellow-black kit'],
                        ],
                        'spinner' => [
                            'dir' => 'spin/crest',
                            'frames' => 36,
                            'caption' => ['fa' => 'نشان سپاهان — نمای ۳۶۰ درجه', 'en' => 'Sepahan crest — 360° view'],
                        ],
                        'kit3d' => [
                            'texture' => 'images/landings/kits/sepahan.png',
                            'accent' => '#F5C518',
                            'secondary' => '#1A1A1E',
                            'caption' => ['fa' => 'کیت خانگی سپاهان — زرد و مشکی', 'en' => 'Sepahan home kit — yellow and black'],

                        ],
'blocks' => [
                            [
                                'type' => 'bullets',
                                'title' => ['fa' => 'سپاهان تو پچ چه داره؟', 'en' => 'What Sepahan gets in the mod'],
                                'items' => [
                                    ['icon' => 'stadium', 'title' => ['fa' => 'استادیوم نقش جهان واقعی', 'en' => 'Real Naqsh-e Jahan Stadium'], 'text' => ['fa' => 'نورپردازی شبانه، چمن و سکوهای اختصاصی سپاهی‌ها بازسازی شده.', 'en' => 'Night lighting, turf and dedicated stands, fully rebuilt.']],
                                    ['icon' => 'fire', 'title' => ['fa' => 'ورودیه و مش هواداری', 'en' => 'Entrance & fan scenes'], 'text' => ['fa' => 'ورودیه اختصاصی با مش‌های هواداری زرد پوش.', 'en' => 'Dedicated intro with yellow supporter meshes.']],
                                    ['icon' => 'crown', 'title' => ['fa' => 'قهرمانی در کاریر', 'en' => 'Career-title run'], 'text' => ['fa' => 'ترکیب و انتقال‌های به‌روز برای فصل واقعی.', 'en' => 'Updated squad and transfers for the real season.']],
                                ],
                            ],
                            [
                                'type' => 'kit3d',
                                'title' => ['fa' => 'کیت سه‌بعدی', 'en' => 'The kit in 3D'],
                                'texture' => 'images/landings/kits/sepahan.png',
                                'accent' => '#F5C518',
                                'secondary' => '#1A1A1E',
                                'caption' => ['fa' => 'کیت خانگی سپاهان — زرد و مشکی', 'en' => 'Sepahan home kit — yellow and black'],
                            ],
                            [
                                'type' => 'spinner',
                                'title' => ['fa' => 'نشان باشگاه ۳۶۰°', 'en' => 'Crest in 360°'],
                                'dir' => 'spin/crest',
                                'frames' => 36,
                                'caption' => ['fa' => 'نشان سپاهان — نمای ۳۶۰ درجه', 'en' => 'Sepahan crest — 360° view'],
                            ],
                            [
                                'type' => 'gallery',
                                'title' => ['fa' => 'اسکرین‌شات‌ها', 'en' => 'Screenshots'],
                                'images' => [
                                    ['src' => 'images/landings/shared/g2-gameplay.svg', 'caption' => ['fa' => 'گیم‌پلی', 'en' => 'Gameplay']],
                                    ['src' => 'images/landings/shared/g4-faces.svg', 'caption' => ['fa' => 'بازیکنان', 'en' => 'Players']],
                                    ['src' => 'images/landings/shared/g5-broadcast.svg', 'caption' => ['fa' => 'روز مسابقه', 'en' => 'Match day']],
                                ],
                            ],
                        ],
                    ],
                    'logo' => 'emb_0084_l.png', 'city' => ['fa' => 'اصفهان', 'en' => 'Isfahan']],
                ['name' => ['fa' => 'تراکتور', 'en' => 'Tractor FC'], 'logo' => 'emb_0111_l.png', 'city' => ['fa' => 'تبریز', 'en' => 'Tabriz']],
                ['name' => ['fa' => 'فولاد خوزستان', 'en' => 'Foolad FC'], 'logo' => 'emb_0047_l.png', 'city' => ['fa' => 'اهواز', 'en' => 'Ahvaz']],
                ['name' => ['fa' => 'ذوب‌آهن', 'en' => 'Zob Ahan Esfahan'], 'logo' => null, 'city' => ['fa' => 'اصفهان', 'en' => 'Isfahan']],
                ['name' => ['fa' => 'گل‌گهر سیرجان', 'en' => 'Gol Gohar Sirjan FC'], 'logo' => null, 'city' => ['fa' => 'سیرجان', 'en' => 'Sirjan']],
                ['name' => ['fa' => 'آلومینیوم اراک', 'en' => 'Aluminium Arak FC'], 'logo' => null, 'city' => ['fa' => 'اراک', 'en' => 'Arak']],
                ['name' => ['fa' => 'ملوان', 'en' => 'Malavan Bandar Anzali'], 'logo' => 'emb_0086_l.png', 'city' => ['fa' => 'بندر انزلی', 'en' => 'Bandar Anzali']],
                ['name' => ['fa' => 'نساجی مازندران', 'en' => 'FC Nassaji Mazandaran'], 'logo' => 'emb_0078_l.png', 'city' => ['fa' => 'قائم‌شهر', 'en' => 'Qaem Shahr']],
                ['name' => ['fa' => 'پیکان', 'en' => 'Paykan FC'], 'logo' => null, 'city' => ['fa' => 'تهران', 'en' => 'Tehran']],
                ['name' => ['fa' => 'صنعت نفت آبادان', 'en' => 'Sanat Naft FC'], 'logo' => 'emb_0120_l.png', 'city' => ['fa' => 'آبادان', 'en' => 'Abadan']],
                ['name' => ['fa' => 'فجر سپاسی شیراز', 'en' => 'Fajr Sepasi Shiraz'], 'logo' => null, 'city' => ['fa' => 'شیراز', 'en' => 'Shiraz']],
                ['name' => ['fa' => 'استقلال خوزستان', 'en' => 'Esteghlal Khuzestan'], 'logo' => null, 'city' => ['fa' => 'اهواز', 'en' => 'Ahvaz']],
                ['name' => ['fa' => 'شمس‌آذر قزوین', 'en' => 'Shams Azar Qazvin'], 'logo' => null, 'city' => ['fa' => 'قزوین', 'en' => 'Qazvin']],
                ['name' => ['fa' => 'خیبر خرم‌آباد', 'en' => 'Kheybar Khorramabad FC'], 'logo' => null, 'city' => ['fa' => 'خرم‌آباد', 'en' => 'Khorramabad']],
                ['name' => ['fa' => 'چادرملو', 'en' => 'Chadormalu SC'], 'logo' => null, 'city' => ['fa' => 'یزد', 'en' => 'Yazd']],
                ['name' => ['fa' => 'مس شهربابک', 'en' => 'Mes Shahr Babak FC'], 'logo' => null, 'city' => ['fa' => 'کرمان', 'en' => 'Kerman']],
            ],
        ],

        'azadegan-league' => [
            'name' => ['fa' => 'لیگ آزادگان', 'en' => 'Azadegan League'],
            'country' => ['fa' => 'ایران', 'en' => 'Iran'],
            'tag' => 'IRN',
            'level' => ['fa' => 'سطح ۲ ایران', 'en' => 'Iran · Tier 2'],
            'teams_count' => 16,
            'logo' => null,
            'description' => [
                'fa' => 'لیگ آزادگان هم با پوشش کامل در پچ حاضر است؛ تیم‌ها، کیت‌ها و چهره‌های کلیدی این لیگ بازسازی شده‌اند تا مسیر صعود در حالت کاریر کاملاً واقعی باشد.',
                'en' => "Iran's second tier is fully covered too — clubs, kits and key player faces are rebuilt so your career-mode promotion battle feels real.",
            ],
            'teams' => [
                ['name' => ['fa' => 'مس کرمان', 'en' => 'Mes Kerman'], 'logo' => null, 'city' => ['fa' => 'کرمان', 'en' => 'Kerman']],
                ['name' => ['fa' => 'سایپا', 'en' => 'Saipa FC'], 'logo' => null, 'city' => ['fa' => 'تهران', 'en' => 'Tehran']],
                ['name' => ['fa' => 'مس رفسنجان', 'en' => 'Mes Rafsanjan'], 'logo' => null, 'city' => ['fa' => 'رفسنجان', 'en' => 'Rafsanjan']],
                ['name' => ['fa' => 'هوادار', 'en' => 'Havadar SC'], 'logo' => null, 'city' => ['fa' => 'اسلامشهر', 'en' => 'Eslamshahr']],
                ['name' => ['fa' => 'پارس جنوبی جام', 'en' => 'Pars Jonoubi Jam'], 'logo' => null, 'city' => ['fa' => 'جام', 'en' => 'Jam']],
                ['name' => ['fa' => 'قشم', 'en' => 'Qeshm Island CSC'], 'logo' => null, 'city' => ['fa' => 'قشم', 'en' => 'Qeshm']],
                ['name' => ['fa' => 'آریو اسلامشهر', 'en' => 'Ario Eslamshahr'], 'logo' => null, 'city' => ['fa' => 'اسلامشهر', 'en' => 'Eslamshahr']],
                ['name' => ['fa' => 'نیروی زمینی', 'en' => 'Niroye Zamini FC'], 'logo' => null, 'city' => ['fa' => 'تهران', 'en' => 'Tehran']],
            ],
        ],

        'premier-league' => [
            'name' => ['fa' => 'لیگ برتر انگلیس', 'en' => 'Premier League'],
            'country' => ['fa' => 'انگلیس', 'en' => 'England'],
            'tag' => 'ENG',
            'level' => ['fa' => 'سطح ۱ انگلیس', 'en' => 'England · Tier 1'],
            'teams_count' => 20,
            'logo' => 'images/landings/logos/emb/emb_0005_l.png',
            'description' => [
                'fa' => 'پرغلبه لیگ دنیا با کیت‌ها، چهره‌ها و استادیوم‌های به‌روز؛ جدول و برنامه مسابقات منطبق بر فصل جاری.',
                'en' => "The world's most-watched league with updated kits, faces and stadiums, plus an authentic season calendar.",
            ],
            'teams' => [
                ['name' => ['fa' => 'منچستریونایتد', 'en' => 'Manchester United'], 'logo' => null, 'city' => ['fa' => 'منچستر', 'en' => 'Manchester']],
                ['name' => ['fa' => 'منچسترسیتی', 'en' => 'Manchester City'], 'logo' => 'emb_0067_l.png', 'city' => ['fa' => 'منچستر', 'en' => 'Manchester']],
                ['name' => ['fa' => 'لیورپول', 'en' => 'Liverpool'], 'logo' => null, 'city' => ['fa' => 'لیورپول', 'en' => 'Liverpool']],
                ['name' => ['fa' => 'آرسنال', 'en' => 'Arsenal'], 'logo' => 'emb_0074_l.png', 'city' => ['fa' => 'لندن', 'en' => 'London']],
                ['name' => ['fa' => 'چلسی', 'en' => 'Chelsea'], 'logo' => null, 'city' => ['fa' => 'لندن', 'en' => 'London']],
                ['name' => ['fa' => 'تاتنهام', 'en' => 'Tottenham'], 'logo' => 'emb_0079_l.png', 'city' => ['fa' => 'لندن', 'en' => 'London']],
                ['name' => ['fa' => 'نیوکاسل', 'en' => 'Newcastle United'], 'logo' => 'emb_0090_l.png', 'city' => ['fa' => 'نیوکاسل', 'en' => 'Newcastle']],
                ['name' => ['fa' => 'استون‌ویلا', 'en' => 'Aston Villa'], 'logo' => null, 'city' => ['fa' => 'بیرمنگام', 'en' => 'Birmingham']],
            ],
        ],

        'laliga' => [
            'name' => ['fa' => 'لالیگا', 'en' => 'LaLiga'],
            'country' => ['fa' => 'اسپانیا', 'en' => 'Spain'],
            'tag' => 'ESP',
            'level' => ['fa' => 'سطح ۱ اسپانیا', 'en' => 'Spain · Tier 1'],
            'teams_count' => 20,
            'logo' => 'images/landings/logos/emb/emb_0008_l.png',
            'description' => [
                'fa' => 'لالیگا با ال‌کلاسیکوی واقعی: کیت‌های فصل جدید، چهره‌های اسکن‌شده و استادیوم‌های اختصاصی رئال و بارسا.',
                'en' => 'LaLiga with a true El Clásico: this season\'s kits, scanned faces and dedicated home stadiums.',
            ],
            'teams' => [
                ['name' => ['fa' => 'رئال‌مادرید', 'en' => 'Real Madrid'],
                    'detail' => [
                        'stats' => [
                            ['label' => ['fa' => 'چهره واقعی بازیکن', 'en' => 'Real player faces'], 'value' => ['fa' => 'کامل ترکیب', 'en' => 'Full squad']],
                            ['label' => ['fa' => 'استادیوم واقعی', 'en' => 'Real stadium'], 'value' => ['fa' => 'برنابئو — دارد', 'en' => 'Bernabéu — Yes']],
                            ['label' => ['fa' => 'چهره مربی', 'en' => 'Coach face'], 'value' => ['fa' => 'دارد', 'en' => 'Yes']],
                        ],
                        'tags' => [
                            ['fa' => 'کیت سفید کلاسیک', 'en' => 'Classic white kit'],
                            ['fa' => 'گیم‌پلی ال‌کلاسیکو', 'en' => 'El Clásico gameplay'],
                        ],
                    ],
                    'logo' => 'emb_0049_l.png', 'city' => ['fa' => 'مادرید', 'en' => 'Madrid']],
                ['name' => ['fa' => 'بارسلونا', 'en' => 'FC Barcelona'], 'logo' => null, 'city' => ['fa' => 'بارسلون', 'en' => 'Barcelona']],
                ['name' => ['fa' => 'اتلتیکومادرید', 'en' => 'Atlético Madrid'], 'logo' => 'emb_0056_l.png', 'city' => ['fa' => 'مادرید', 'en' => 'Madrid']],
                ['name' => ['fa' => 'سویا', 'en' => 'Sevilla'], 'logo' => null, 'city' => ['fa' => 'سویا', 'en' => 'Seville']],
                ['name' => ['fa' => 'والنسیا', 'en' => 'Valencia'], 'logo' => null, 'city' => ['fa' => 'والنسیا', 'en' => 'Valencia']],
                ['name' => ['fa' => 'بیلبائو', 'en' => 'Athletic Club'], 'logo' => null, 'city' => ['fa' => 'بیلبائو', 'en' => 'Bilbao']],
            ],
        ],

        'serie-a' => [
            'name' => ['fa' => 'سری آ', 'en' => 'Serie A'],
            'country' => ['fa' => 'ایتالیا', 'en' => 'Italy'],
            'tag' => 'ITA',
            'level' => ['fa' => 'سطح ۱ ایتالیا', 'en' => 'Italy · Tier 1'],
            'teams_count' => 20,
            'logo' => 'images/landings/logos/emb/emb_0009_l.png',
            'description' => [
                'fa' => 'کالچو با تاکتیک و گیم‌پلی سنگین؛ تیم‌های بزرگ ایتالیا با کیت و چهره‌های کامل.',
                'en' => 'Calcio with its trademark tactical weight — Italy\'s giants fully rebuilt.',
            ],
            'teams' => [
                ['name' => ['fa' => 'یوونتوس', 'en' => 'Juventus'], 'logo' => null, 'city' => ['fa' => 'تورین', 'en' => 'Turin']],
                ['name' => ['fa' => 'اینتر', 'en' => 'Inter Milan'], 'logo' => null, 'city' => ['fa' => 'میلان', 'en' => 'Milan']],
                ['name' => ['fa' => 'آث میلان', 'en' => 'AC Milan'], 'logo' => null, 'city' => ['fa' => 'میلان', 'en' => 'Milan']],
                ['name' => ['fa' => 'رم', 'en' => 'AS Roma'], 'logo' => 'emb_0131_l.png', 'city' => ['fa' => 'رم', 'en' => 'Rome']],
                ['name' => ['fa' => 'ناپولی', 'en' => 'Napoli'], 'logo' => null, 'city' => ['fa' => 'ناپولی', 'en' => 'Naples']],
                ['name' => ['fa' => 'لاتزیو', 'en' => 'Lazio'], 'logo' => null, 'city' => ['fa' => 'رم', 'en' => 'Rome']],
            ],
        ],

        'bundesliga' => [
            'name' => ['fa' => 'بوندسلیگا', 'en' => 'Bundesliga'],
            'country' => ['fa' => 'آلمان', 'en' => 'Germany'],
            'tag' => 'GER',
            'level' => ['fa' => 'سطح ۱ آلمان', 'en' => 'Germany · Tier 1'],
            'teams_count' => 18,
            'logo' => 'images/landings/logos/emb/emb_0009_l.png',
            'description' => [
                'fa' => 'بوندسلیگای پرهیجان با فوتبال تهاجمی و استادیوم‌های پرمخاطب.',
                'en' => 'High-tempo Bundesliga football with its famous full houses.',
            ],
            'teams' => [
                ['name' => ['fa' => 'بایرن‌مونیخ', 'en' => 'Bayern München'], 'logo' => null, 'city' => ['fa' => 'مونیخ', 'en' => 'Munich']],
                ['name' => ['fa' => 'دورتموند', 'en' => 'Borussia Dortmund'], 'logo' => null, 'city' => ['fa' => 'دورتموند', 'en' => 'Dortmund']],
                ['name' => ['fa' => 'لورکوزن', 'en' => 'Bayer Leverkusen'], 'logo' => null, 'city' => ['fa' => 'لورکوزن', 'en' => 'Leverkusen']],
                ['name' => ['fa' => 'لایپزیگ', 'en' => 'RB Leipzig'], 'logo' => null, 'city' => ['fa' => 'لایپزیگ', 'en' => 'Leipzig']],
            ],
        ],

        'ligue-1' => [
            'name' => ['fa' => 'لیگ ۱ فرانسه', 'en' => 'Ligue 1'],
            'country' => ['fa' => 'فرانسه', 'en' => 'France'],
            'tag' => 'FRA',
            'level' => ['fa' => 'سطح ۱ فرانسه', 'en' => 'France · Tier 1'],
            'teams_count' => 18,
            'logo' => null,
            'description' => [
                'fa' => 'لیگ ۱ با ستاره‌های بزرگ و فوتبال فنی؛ پاریس در قلب آن.',
                'en' => 'Ligue 1 with its stars and technical football — Paris at the heart.',
            ],
            'teams' => [
                ['name' => ['fa' => 'پاری‌سن‌ژرمن', 'en' => 'Paris Saint-Germain'], 'logo' => null, 'city' => ['fa' => 'پاریس', 'en' => 'Paris']],
                ['name' => ['fa' => 'مارسی', 'en' => 'Olympique Marseille'], 'logo' => null, 'city' => ['fa' => 'مارسی', 'en' => 'Marseille']],
                ['name' => ['fa' => 'لیون', 'en' => 'Olympique Lyon'], 'logo' => null, 'city' => ['fa' => 'لیون', 'en' => 'Lyon']],
                ['name' => ['fa' => 'موناکو', 'en' => 'AS Monaco'], 'logo' => null, 'city' => ['fa' => 'موناکو', 'en' => 'Monaco']],
            ],
        ],

        'uefa-champions-league' => [
            'name' => ['fa' => 'لیگ قهرمانان اروپا', 'en' => 'UEFA Champions League'],
            'country' => ['fa' => 'اروپا', 'en' => 'Europe'],
            'tag' => 'UCL',
            'level' => ['fa' => 'جام باشگاه‌های اروپا', 'en' => 'Continental Cup'],
            'teams_count' => 36,
            'logo' => 'images/landings/logos/emb/emb_0001_l.png',
            'description' => [
                'fa' => 'شب‌های جادویی اروپا با جام، موسیقی ورود و گرافیک اختصاصی پخش؛ تجربه واقعی UCL از گروهی تا فینال.',
                'en' => 'European nights with the iconic trophy, anthem-style presentation and dedicated broadcast graphics from group stage to the final.',
            ],
            'teams' => [
                ['name' => ['fa' => 'رئال‌مادرید', 'en' => 'Real Madrid'], 'logo' => 'emb_0049_l.png', 'city' => ['fa' => 'اسپانیا', 'en' => 'Spain']],
                ['name' => ['fa' => 'مانچسترسیتی', 'en' => 'Manchester City'], 'logo' => 'emb_0067_l.png', 'city' => ['fa' => 'انگلیس', 'en' => 'England']],
                ['name' => ['fa' => 'بایرن‌مونیخ', 'en' => 'Bayern München'], 'logo' => null, 'city' => ['fa' => 'آلمان', 'en' => 'Germany']],
                ['name' => ['fa' => 'پاری‌سن‌ژرمن', 'en' => 'Paris Saint-Germain'], 'logo' => null, 'city' => ['fa' => 'فرانسه', 'en' => 'France']],
                ['name' => ['fa' => 'اینتر', 'en' => 'Inter Milan'], 'logo' => null, 'city' => ['fa' => 'ایتالیا', 'en' => 'Italy']],
                ['name' => ['fa' => 'بارسلونا', 'en' => 'FC Barcelona'], 'logo' => null, 'city' => ['fa' => 'اسپانیا', 'en' => 'Spain']],
            ],
        ],
    ],


    /*
    |----------------------------------------------------------------------
    | نشان‌های کره سه‌بعدی — {league, lat, lng} + مناطق/تیم‌ها برای پنل
    |----------------------------------------------------------------------
    */
    'globe' => [
        [
            'slug' => 'persian-gulf-pro-league', 'countryKey' => 'Iran', 'lat' => 35.5, 'lng' => 53.5,
            'name' => ['fa' => 'ایران — لیگ برتر خلیج فارس', 'en' => 'Iran — Persian Gulf Pro League'],
            'country' => ['fa' => 'ایران', 'en' => 'Iran'],
            'regions' => [
                ['name' => ['fa' => 'تهران', 'en' => 'Tehran'], 'teams' => [
                    ['name' => ['fa' => 'پرسپولیس', 'en' => 'Persepolis FC'], 'lat' => 35.69, 'lng' => 51.37, 'city' => ['fa' => 'تهران', 'en' => 'Tehran'], 'logo' => null],
                    ['name' => ['fa' => 'استقلال', 'en' => 'Esteghlal FC'], 'lat' => 35.71, 'lng' => 51.43, 'city' => ['fa' => 'تهران', 'en' => 'Tehran'], 'logo' => 'emb_0112_l.png'],
                    ['name' => ['fa' => 'پیکان', 'en' => 'Paykan FC'], 'lat' => 35.76, 'lng' => 51.5, 'city' => ['fa' => 'تهران', 'en' => 'Tehran'], 'logo' => null],
                ]],
                ['name' => ['fa' => 'اصفهان', 'en' => 'Isfahan'], 'teams' => [
                    ['name' => ['fa' => 'سپاهان', 'en' => 'Sepahan FC'], 'lat' => 32.62, 'lng' => 51.63, 'city' => ['fa' => 'اصفهان', 'en' => 'Isfahan'], 'logo' => 'emb_0084_l.png'],
                    ['name' => ['fa' => 'ذوب‌آهن', 'en' => 'Zob Ahan Esfahan'], 'lat' => 32.72, 'lng' => 51.72, 'city' => ['fa' => 'اصفهان', 'en' => 'Isfahan'], 'logo' => null],
                ]],
                ['name' => ['fa' => 'آذربایجان شرقی', 'en' => 'East Azerbaijan'], 'teams' => [
                    ['name' => ['fa' => 'تراکتور', 'en' => 'Tractor FC'], 'lat' => 38.05, 'lng' => 46.29, 'city' => ['fa' => 'تبریز', 'en' => 'Tabriz'], 'logo' => 'emb_0111_l.png'],
                ]],
                ['name' => ['fa' => 'خوزستان', 'en' => 'Khuzestan'], 'teams' => [
                    ['name' => ['fa' => 'فولاد', 'en' => 'Foolad FC'], 'lat' => 31.32, 'lng' => 48.67, 'city' => ['fa' => 'اهواز', 'en' => 'Ahvaz'], 'logo' => 'emb_0047_l.png'],
                ]],
                ['name' => ['fa' => 'مازندران', 'en' => 'Mazandaran'], 'teams' => [
                    ['name' => ['fa' => 'نساجی', 'en' => 'FC Nassaji Mazandaran'], 'lat' => 36.47, 'lng' => 52.86, 'city' => ['fa' => 'قائم‌شهر', 'en' => 'Qaem Shahr'], 'logo' => 'emb_0078_l.png'],
                ]],
                ['name' => ['fa' => 'گیلان', 'en' => 'Gilan'], 'teams' => [
                    ['name' => ['fa' => 'ملوان', 'en' => 'Malavan Bandar Anzali'], 'lat' => 37.47, 'lng' => 49.46, 'city' => ['fa' => 'بندر انزلی', 'en' => 'Bandar Anzali'], 'logo' => 'emb_0086_l.png'],
                ]],
            ],
        ],
        [
            'slug' => 'premier-league', 'countryKey' => 'United Kingdom', 'lat' => 52.6, 'lng' => -1.6,
            'name' => ['fa' => 'انگلیس — لیگ برتر', 'en' => 'England — Premier League'],
            'country' => ['fa' => 'انگلیس', 'en' => 'England'],
            'regions' => [
                ['name' => ['fa' => 'انگلیس', 'en' => 'England'], 'teams' => [
                    ['name' => ['fa' => 'منچسترسیتی', 'en' => 'Manchester City'], 'lat' => 53.48, 'lng' => -2.24, 'city' => ['fa' => 'منچستر', 'en' => 'Manchester'], 'logo' => 'emb_0067_l.png'],
                    ['name' => ['fa' => 'آرسنال', 'en' => 'Arsenal'], 'lat' => 51.55, 'lng' => -0.08, 'city' => ['fa' => 'لندن', 'en' => 'London'], 'logo' => 'emb_0074_l.png'],
                    ['name' => ['fa' => 'تاتنهام', 'en' => 'Tottenham'], 'lat' => 51.6, 'lng' => -0.03, 'city' => ['fa' => 'لندن', 'en' => 'London'], 'logo' => 'emb_0079_l.png'],
                    ['name' => ['fa' => 'نیوکاسل', 'en' => 'Newcastle United'], 'lat' => 54.98, 'lng' => -1.62, 'city' => ['fa' => 'نیوکاسل', 'en' => 'Newcastle United'], 'logo' => 'emb_0090_l.png'],
                ]],
            ],
        ],
        [
            'slug' => 'laliga', 'countryKey' => 'Spain', 'lat' => 40.2, 'lng' => -3.7,
            'name' => ['fa' => 'اسپانیا — لالیگا', 'en' => 'Spain — LaLiga'],
            'country' => ['fa' => 'اسپانیا', 'en' => 'Spain'],
            'regions' => [
                ['name' => ['fa' => 'اسپانیا', 'en' => 'Spain'], 'teams' => [
                    ['name' => ['fa' => 'رئال‌مادرید', 'en' => 'Real Madrid'], 'lat' => 40.45, 'lng' => -3.69, 'city' => ['fa' => 'مادرید', 'en' => 'Madrid'], 'logo' => 'emb_0049_l.png'],
                    ['name' => ['fa' => 'اتلتیکومادرید', 'en' => 'Atletico Madrid'], 'lat' => 40.43, 'lng' => -3.55, 'city' => ['fa' => 'مادرید', 'en' => 'Madrid'], 'logo' => 'emb_0056_l.png'],
                ]],
            ],
        ],
        [
            'slug' => 'serie-a', 'countryKey' => 'Italy', 'lat' => 42.8, 'lng' => 12.5,
            'name' => ['fa' => 'ایتالیا — سری آ', 'en' => 'Italy — Serie A'],
            'country' => ['fa' => 'ایتالیا', 'en' => 'Italy'],
            'regions' => [
                ['name' => ['fa' => 'ایتالیا', 'en' => 'Italy'], 'teams' => [
                    ['name' => ['fa' => 'رم', 'en' => 'AS Roma'], 'lat' => 41.89, 'lng' => 12.49, 'city' => ['fa' => 'رم', 'en' => 'Rome'], 'logo' => 'emb_0131_l.png'],
                ]],
            ],
        ],
        [
            'slug' => 'bundesliga', 'countryKey' => 'Germany', 'lat' => 51.1, 'lng' => 10.4,
            'name' => ['fa' => 'آلمان — بوندسلیگا', 'en' => 'Germany — Bundesliga'],
            'country' => ['fa' => 'آلمان', 'en' => 'Germany'],
            'regions' => [
                ['name' => ['fa' => 'آلمان', 'en' => 'Germany'], 'teams' => [
                    ['name' => ['fa' => 'بایرن‌مونیخ', 'en' => 'Bayern Munchen'], 'lat' => 48.14, 'lng' => 11.58, 'city' => ['fa' => 'مونیخ', 'en' => 'Munich'], 'logo' => null],
                    ['name' => ['fa' => 'دورتموند', 'en' => 'Borussia Dortmund'], 'lat' => 51.51, 'lng' => 7.46, 'city' => ['fa' => 'دورتموند', 'en' => 'Borussia Dortmund'], 'logo' => null],
                ]],
            ],
        ],
    ],

    'stadiums' => [
        ['name' => ['fa' => 'آزادی', 'en' => 'Azadi Stadium'], 'city' => ['fa' => 'تهران', 'en' => 'Tehran'], 'capacity' => 78116, 'team' => ['fa' => 'پرسپولیس و استقلال', 'en' => 'Persepolis & Esteghlal'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'نقش جهان', 'en' => 'Naqsh-e Jahan Stadium'], 'city' => ['fa' => 'اصفهان', 'en' => 'Isfahan'], 'capacity' => 75000, 'team' => ['fa' => 'سپاهان', 'en' => 'Sepahan'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'سهند', 'en' => 'Sahand Stadium'], 'city' => ['fa' => 'تبریز', 'en' => 'Tabriz'], 'capacity' => 68000, 'team' => ['fa' => 'تراکتور', 'en' => 'Tractor'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'غدیر', 'en' => 'Ghadir Stadium'], 'city' => ['fa' => 'اهواز', 'en' => 'Ahvaz'], 'capacity' => 38000, 'team' => ['fa' => 'فولاد و استقلال خوزستان', 'en' => 'Foolad & Est. Khuzestan'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'ملوان', 'en' => 'Malavan Stadium'], 'city' => ['fa' => 'بندر انزلی', 'en' => 'Bandar Anzali'], 'capacity' => 12000, 'team' => ['fa' => 'ملوان', 'en' => 'Malavan'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'گل‌گهر', 'en' => 'Gol Gohar Stadium'], 'city' => ['fa' => 'سیرجان', 'en' => 'Sirjan'], 'capacity' => 15000, 'team' => ['fa' => 'گل‌گهر', 'en' => 'Gol Gohar'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'ونبل', 'en' => 'Wembley'], 'city' => ['fa' => 'لندن', 'en' => 'London'], 'capacity' => 90000, 'team' => ['fa' => 'تیم ملی انگلیس', 'en' => 'England NT'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'سن‌سیرو', 'en' => 'San Siro'], 'city' => ['fa' => 'میلان', 'en' => 'Milan'], 'capacity' => 80018, 'team' => ['fa' => 'میلان و اینتر', 'en' => 'Milan & Inter'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'آلیانزآرنا', 'en' => 'Allianz Arena'], 'city' => ['fa' => 'مونیخ', 'en' => 'Munich'], 'capacity' => 75000, 'team' => ['fa' => 'بایرن‌مونیخ', 'en' => 'Bayern'], 'image' => 'images/landings/shared/g3-stadium.svg'],
        ['name' => ['fa' => 'کامپ‌نو', 'en' => 'Camp Nou'], 'city' => ['fa' => 'بارسلون', 'en' => 'Barcelona'], 'capacity' => 99354, 'team' => ['fa' => 'بارسلونا', 'en' => 'FC Barcelona'], 'image' => 'images/landings/shared/g3-stadium.svg'],
    ],

    'trophies' => [
        ['name' => ['fa' => 'جام لیگ برتر خلیج فارس', 'en' => 'Persian Gulf Pro League Trophy'], 'competition' => ['fa' => 'ایران', 'en' => 'Iran'], 'detail' => ['fa' => 'مدل سه‌بعدی اختصاصی جام لیگ برتر با مراسم اهدای ایرانی', 'en' => 'Custom 3D model with an authentic Iranian celebration scene']],
        ['name' => ['fa' => 'جام حذفی ایران', 'en' => 'Hazfi Cup'], 'competition' => ['fa' => 'ایران', 'en' => 'Iran'], 'detail' => ['fa' => 'جام حذفی با مسیر کامل از مرحله اول', 'en' => 'Full knockout path from round one']],
        ['name' => ['fa' => 'سوپرجام ایران', 'en' => 'Iranian Super Cup'], 'competition' => ['fa' => 'ایران', 'en' => 'Iran'], 'detail' => ['fa' => 'دیدگاه قهرمان لیگ و جام حذفی', 'en' => 'Champions of league vs cup']],
        ['name' => ['fa' => 'جام قهرمانان اروپا', 'en' => 'UEFA Champions League Trophy'], 'competition' => ['fa' => 'اروپا', 'en' => 'Europe'], 'detail' => ['fa' => 'جام معروف UCL با «بیگ‌ایار» واقعی', 'en' => 'The iconic big-eared cup']],
        ['name' => ['fa' => 'جام لیگ اروپا', 'en' => 'UEFA Europa League Trophy'], 'competition' => ['fa' => 'اروپا', 'en' => 'Europe'], 'detail' => ['fa' => 'جام بدون دسته لیگ اروپا', 'en' => 'The handleless UEL cup']],
        ['name' => ['fa' => 'جام لیگ برتر انگلیس', 'en' => 'Premier League Trophy'], 'competition' => ['fa' => 'انگلیس', 'en' => 'England'], 'detail' => ['fa' => 'جام طلایی پریمیرلیگ با روبان', 'en' => 'The golden crown jewel']],
        ['name' => ['fa' => 'جام لالیگا', 'en' => 'LaLiga Trophy'], 'competition' => ['fa' => 'اسپانیا', 'en' => 'Spain'], 'detail' => ['fa' => 'جام سبز رنگ لالیگا', 'en' => 'The green LFP trophy']],
        ['name' => ['fa' => 'جام بوندسلیگا', 'en' => 'Bundesliga Trophy'], 'competition' => ['fa' => 'آلمان', 'en' => 'Germany'], 'detail' => ['fa' => 'کاسه معروف نقره‌ای بوندسلیگا', 'en' => 'The legendary silver salad bowl']],
    ],
];
