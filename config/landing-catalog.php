<?php

/*
|----------------------------------------------------------------------|
| کاتالوگ مشترک لندینگ‌ها — لیگ‌ها/تیم‌ها/استادیوم‌ها/جام‌ها (دوزبانه)  |
| pgl_team_id: شناسه‌ی تیم در دیتابیس بازیکنان پچ (برای نمایش ترکیب)   |
| توجه: نام‌های Team.bin پچ با اسکوادها ممکن است نخوانند؛ نگاشت را      |
| با دیدن ترکیب تیم در /players/t/{id} تأیید کنید.                     |
|----------------------------------------------------------------------|
*/

return array (
  'leagues' =>
  array (
    'persian-gulf-pro-league' =>
    array (
      'name' =>
      array (
        'fa' => 'لیگ برتر خلیج فارس',
        'en' => 'Persian Gulf Pro League',
      ),
      'country' =>
      array (
        'fa' => 'ایران',
        'en' => 'Iran',
      ),
      'tag' => 'IRN',
      'level' =>
      array (
        'fa' => 'سطح ۱ ایران',
        'en' => 'Iran · Tier 1',
      ),
      'teams_count' => 18,
      'logo' => 'images/landings/logos/emb/emb_0001_l.png',
      'description' =>
      array (
        'fa' => 'کامل‌ترین بازسازی لیگ برتر خلیج فارس در تاریخ پچ‌های فارسی: هر ۱۸ تیم با کیت‌های رسمی، چهره‌های سه‌بعدی بازیکنان، استادیوم‌های اختصاصی، گزارش فارسی و اسکوربورد شبکه‌های ورزشی ایران. جدول لیگ، قانون بازیافت و ساختار فصل کاملاً منطبق بر واقعیت است.',
        'en' => 'The most complete rebuild of the Persian Gulf Pro League ever made in a Persian patch: all 18 clubs with authentic kits, 3D player faces, dedicated stadiums, Persian commentary and Iranian TV graphics — with a fully authentic league table and season structure.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'پرسپولیس',
            'en' => 'Persepolis FC',
          ),
          'detail' =>
          array (
            'stats' =>
            array (
              0 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره واقعی بازیکن',
                  'en' => 'Real player faces',
                ),
                'value' =>
                array (
                  'fa' => '۲۶ بازیکن',
                  'en' => '26 players',
                ),
              ),
              1 =>
              array (
                'label' =>
                array (
                  'fa' => 'استادیوم واقعی',
                  'en' => 'Real stadium',
                ),
                'value' =>
                array (
                  'fa' => 'آزادی — دارد',
                  'en' => 'Azadi — Yes',
                ),
              ),
              2 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره مربی',
                  'en' => 'Coach face',
                ),
                'value' =>
                array (
                  'fa' => 'دارد',
                  'en' => 'Yes',
                ),
              ),
            ),
            'description' =>
            array (
              'fa' => 'پرطرفدارترین تیم ایران با کامل‌ترین پوشش پچ: چهره‌های سه‌بعدی ۲۶ بازیکن اصلی و ذخیره، کیت‌های فصل با اسپانسر، ورودیه اختصاصی آزادی و ورق‌های تشویقی سکوها.',
              'en' => 'The best-supported club in Iran with the fullest coverage: 3D faces for 26 players, sponsored kits, a dedicated Azadi entrance scene and crowd cards.',
            ),
            'tags' =>
            array (
              0 =>
              array (
                'fa' => 'گیم‌پلی ترکیب اصلی',
                'en' => 'First-XI gameplay',
              ),
              1 =>
              array (
                'fa' => 'ورودیه آزادی',
                'en' => 'Azadi entrance',
              ),
              2 =>
              array (
                'fa' => 'کیت‌های اسپانسردار',
                'en' => 'Sponsored kits',
              ),
            ),
            'kit3d' =>
            array (
              'texture' => 'images/landings/kits/persepolis.png',
              'accent' => '#C41622',
              'secondary' => '#8E0F1A',
              'caption' =>
              array (
                'fa' => 'کیت خانگی پرسپولیس — قرمز با شاش سفید',
                'en' => 'Persepolis home kit — red with white sash',
              ),
            ),
            'blocks' =>
            array (
              0 =>
              array (
                'type' => 'video',
                'title' =>
                array (
                  'fa' => 'پیش‌نمایش ویدیویی',
                  'en' => 'Video preview',
                ),
                'url' => NULL,
                'poster' => 'images/landings/shared/g2-gameplay.svg',
                'caption' =>
                array (
                  'fa' => 'تریلر پرسپولیس تو پچ — به‌زودی',
                  'en' => 'Persepolis in the mod — trailer coming soon',
                ),
              ),
              1 =>
              array (
                'type' => 'bullets',
                'title' =>
                array (
                  'fa' => 'چرا پرسپولیس تو این پچ خاصه؟',
                  'en' => 'Why Persepolis feels special here',
                ),
                'items' =>
                array (
                  0 =>
                  array (
                    'icon' => 'crown',
                    'title' =>
                    array (
                      'fa' => 'کامل‌ترین پوشش چهره‌ها',
                      'en' => 'Fullest face coverage',
                    ),
                    'text' =>
                    array (
                      'fa' => '۲۶ بازیکن با سر سه‌بعدی اسکن‌شده و مینی‌فیس.',
                      'en' => '26 players with scanned 3D heads and minifaces.',
                    ),
                  ),
                  1 =>
                  array (
                    'icon' => 'fire',
                    'title' =>
                    array (
                      'fa' => 'ورودیه آزادی',
                      'en' => 'Azadi entrance',
                    ),
                    'text' =>
                    array (
                      'fa' => 'دقیقاً همون حس شب‌های پرسپولیس.',
                      'en' => 'The exact feeling of Persepolis nights.',
                    ),
                  ),
                  2 =>
                  array (
                    'icon' => 'star',
                    'title' =>
                    array (
                      'fa' => 'ستاره‌های تیم ملی',
                      'en' => 'National-team stars',
                    ),
                    'text' =>
                    array (
                      'fa' => 'چهره‌های واقعی ستاره‌های ملی داخل تیم.',
                      'en' => 'Real faces of the national stars in the squad.',
                    ),
                  ),
                ),
              ),
              2 =>
              array (
                'type' => 'kit3d',
                'title' =>
                array (
                  'fa' => 'کیت سه‌بعدی',
                  'en' => 'The kit in 3D',
                ),
                'texture' => 'images/landings/kits/persepolis.png',
                'accent' => '#C41622',
                'secondary' => '#8E0F1A',
                'caption' =>
                array (
                  'fa' => 'کیت خانگی پرسپولیس — قرمز با شاش سفید',
                  'en' => 'Persepolis home kit — red with white sash',
                ),
              ),
              3 =>
              array (
                'type' => 'gallery',
                'title' =>
                array (
                  'fa' => 'از داخل بازی',
                  'en' => 'In-game',
                ),
                'images' =>
                array (
                  0 =>
                  array (
                    'src' => 'images/landings/shared/g2-gameplay.svg',
                    'caption' =>
                    array (
                      'fa' => 'گیم‌پلی',
                      'en' => 'Gameplay',
                    ),
                  ),
                  1 =>
                  array (
                    'src' => 'images/landings/shared/g1-menu.svg',
                    'caption' =>
                    array (
                      'fa' => 'منو',
                      'en' => 'Menu',
                    ),
                  ),
                ),
              ),
            ),
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'تهران',
            'en' => 'Tehran',
          ),
          'pgl_team_id' => 4951,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'استقلال',
            'en' => 'Esteghlal FC',
          ),
          'detail' =>
          array (
            'stats' =>
            array (
              0 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره واقعی بازیکن',
                  'en' => 'Real player faces',
                ),
                'value' =>
                array (
                  'fa' => '۲۳ بازیکن',
                  'en' => '23 players',
                ),
              ),
              1 =>
              array (
                'label' =>
                array (
                  'fa' => 'استادیوم واقعی',
                  'en' => 'Real stadium',
                ),
                'value' =>
                array (
                  'fa' => 'آزادی — دارد',
                  'en' => 'Azadi — Yes',
                ),
              ),
              2 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره مربی',
                  'en' => 'Coach face',
                ),
                'value' =>
                array (
                  'fa' => 'دارد',
                  'en' => 'Yes',
                ),
              ),
            ),
            'description' =>
            array (
              'fa' => 'آبی‌پوشان پایتخت با چهره‌های واقعی ۲۳ بازیکن، کیت‌های آبی فصل جدید و مش‌های هواداری سکوی اختصاصی.',
              'en' => 'The capital blues with 23 real faces, this-season kits and dedicated supporter meshes.',
            ),
            'tags' =>
            array (
              0 =>
              array (
                'fa' => 'گیم‌پلی ترکیب اصلی',
                'en' => 'First-XI gameplay',
              ),
              1 =>
              array (
                'fa' => 'کیت فصل جدید',
                'en' => 'New-season kit',
              ),
            ),
            'kit3d' =>
            array (
              'texture' => 'images/landings/kits/esteghlal.png',
              'accent' => '#3A7BD5',
              'secondary' => '#17408B',
              'caption' =>
              array (
                'fa' => 'کیت خانگی استقلال — آبی راه‌راه',
                'en' => 'Esteghlal home kit — blue hoops',
              ),
            ),
          ),
          'logo' => 'emb_0112_l.png',
          'city' =>
          array (
            'fa' => 'تهران',
            'en' => 'Tehran',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'سپاهان',
            'en' => 'Sepahan FC',
          ),
          'detail' =>
          array (
            'stats' =>
            array (
              0 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره واقعی بازیکن',
                  'en' => 'Real player faces',
                ),
                'value' =>
                array (
                  'fa' => '۱۹ بازیکن',
                  'en' => '19 players',
                ),
              ),
              1 =>
              array (
                'label' =>
                array (
                  'fa' => 'استادیوم واقعی',
                  'en' => 'Real stadium',
                ),
                'value' =>
                array (
                  'fa' => 'نقش جهان — دارد',
                  'en' => 'Naqsh-e Jahan — Yes',
                ),
              ),
              2 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره مربی',
                  'en' => 'Coach face',
                ),
                'value' =>
                array (
                  'fa' => 'دارد',
                  'en' => 'Yes',
                ),
              ),
            ),
            'description' =>
            array (
              'fa' => 'زردهای اصفهان با استادیوم نقش جهان بازسازی‌شده، چهره‌های واقعی و کیت‌های زرد-سیاه فصل.',
              'en' => 'The yellows of Isfahan with the rebuilt Naqsh-e Jahan stadium, real faces and yellow-black kits.',
            ),
            'tags' =>
            array (
              0 =>
              array (
                'fa' => 'استادیوم نقش جهان',
                'en' => 'Naqsh-e Jahan',
              ),
              1 =>
              array (
                'fa' => 'کیت زرد-سیاه',
                'en' => 'Yellow-black kit',
              ),
            ),
            'spinner' =>
            array (
              'dir' => 'spin/crest',
              'frames' => 36,
              'caption' =>
              array (
                'fa' => 'نشان سپاهان — نمای ۳۶۰ درجه',
                'en' => 'Sepahan crest — 360° view',
              ),
            ),
            'kit3d' =>
            array (
              'texture' => 'images/landings/kits/sepahan.png',
              'accent' => '#F5C518',
              'secondary' => '#1A1A1E',
              'caption' =>
              array (
                'fa' => 'کیت خانگی سپاهان — زرد و مشکی',
                'en' => 'Sepahan home kit — yellow and black',
              ),
            ),
            'blocks' =>
            array (
              0 =>
              array (
                'type' => 'bullets',
                'title' =>
                array (
                  'fa' => 'سپاهان تو پچ چه داره؟',
                  'en' => 'What Sepahan gets in the mod',
                ),
                'items' =>
                array (
                  0 =>
                  array (
                    'icon' => 'stadium',
                    'title' =>
                    array (
                      'fa' => 'استادیوم نقش جهان واقعی',
                      'en' => 'Real Naqsh-e Jahan Stadium',
                    ),
                    'text' =>
                    array (
                      'fa' => 'نورپردازی شبانه، چمن و سکوهای اختصاصی سپاهی‌ها بازسازی شده.',
                      'en' => 'Night lighting, turf and dedicated stands, fully rebuilt.',
                    ),
                  ),
                  1 =>
                  array (
                    'icon' => 'fire',
                    'title' =>
                    array (
                      'fa' => 'ورودیه و مش هواداری',
                      'en' => 'Entrance & fan scenes',
                    ),
                    'text' =>
                    array (
                      'fa' => 'ورودیه اختصاصی با مش‌های هواداری زرد پوش.',
                      'en' => 'Dedicated intro with yellow supporter meshes.',
                    ),
                  ),
                  2 =>
                  array (
                    'icon' => 'crown',
                    'title' =>
                    array (
                      'fa' => 'قهرمانی در کاریر',
                      'en' => 'Career-title run',
                    ),
                    'text' =>
                    array (
                      'fa' => 'ترکیب و انتقال‌های به‌روز برای فصل واقعی.',
                      'en' => 'Updated squad and transfers for the real season.',
                    ),
                  ),
                ),
              ),
              1 =>
              array (
                'type' => 'kit3d',
                'title' =>
                array (
                  'fa' => 'کیت سه‌بعدی',
                  'en' => 'The kit in 3D',
                ),
                'texture' => 'images/landings/kits/sepahan.png',
                'accent' => '#F5C518',
                'secondary' => '#1A1A1E',
                'caption' =>
                array (
                  'fa' => 'کیت خانگی سپاهان — زرد و مشکی',
                  'en' => 'Sepahan home kit — yellow and black',
                ),
              ),
              2 =>
              array (
                'type' => 'spinner',
                'title' =>
                array (
                  'fa' => 'نشان باشگاه ۳۶۰°',
                  'en' => 'Crest in 360°',
                ),
                'dir' => 'spin/crest',
                'frames' => 36,
                'caption' =>
                array (
                  'fa' => 'نشان سپاهان — نمای ۳۶۰ درجه',
                  'en' => 'Sepahan crest — 360° view',
                ),
              ),
              3 =>
              array (
                'type' => 'gallery',
                'title' =>
                array (
                  'fa' => 'اسکرین‌شات‌ها',
                  'en' => 'Screenshots',
                ),
                'images' =>
                array (
                  0 =>
                  array (
                    'src' => 'images/landings/shared/g2-gameplay.svg',
                    'caption' =>
                    array (
                      'fa' => 'گیم‌پلی',
                      'en' => 'Gameplay',
                    ),
                  ),
                  1 =>
                  array (
                    'src' => 'images/landings/shared/g4-faces.svg',
                    'caption' =>
                    array (
                      'fa' => 'بازیکنان',
                      'en' => 'Players',
                    ),
                  ),
                  2 =>
                  array (
                    'src' => 'images/landings/shared/g5-broadcast.svg',
                    'caption' =>
                    array (
                      'fa' => 'روز مسابقه',
                      'en' => 'Match day',
                    ),
                  ),
                ),
              ),
            ),
          ),
          'logo' => 'emb_0084_l.png',
          'city' =>
          array (
            'fa' => 'اصفهان',
            'en' => 'Isfahan',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'تراکتور',
            'en' => 'Tractor FC',
          ),
          'logo' => 'emb_0111_l.png',
          'city' =>
          array (
            'fa' => 'تبریز',
            'en' => 'Tabriz',
          ),
          'pgl_team_id' => NULL,
        ),
        4 =>
        array (
          'name' =>
          array (
            'fa' => 'فولاد خوزستان',
            'en' => 'Foolad FC',
          ),
          'logo' => 'emb_0047_l.png',
          'city' =>
          array (
            'fa' => 'اهواز',
            'en' => 'Ahvaz',
          ),
          'pgl_team_id' => NULL,
        ),
        5 =>
        array (
          'name' =>
          array (
            'fa' => 'ذوب‌آهن',
            'en' => 'Zob Ahan Esfahan',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'اصفهان',
            'en' => 'Isfahan',
          ),
          'pgl_team_id' => NULL,
        ),
        6 =>
        array (
          'name' =>
          array (
            'fa' => 'گل‌گهر سیرجان',
            'en' => 'Gol Gohar Sirjan FC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'سیرجان',
            'en' => 'Sirjan',
          ),
          'pgl_team_id' => NULL,
        ),
        7 =>
        array (
          'name' =>
          array (
            'fa' => 'آلومینیوم اراک',
            'en' => 'Aluminium Arak FC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'اراک',
            'en' => 'Arak',
          ),
          'pgl_team_id' => NULL,
        ),
        8 =>
        array (
          'name' =>
          array (
            'fa' => 'ملوان',
            'en' => 'Malavan Bandar Anzali',
          ),
          'logo' => 'emb_0086_l.png',
          'city' =>
          array (
            'fa' => 'بندر انزلی',
            'en' => 'Bandar Anzali',
          ),
          'pgl_team_id' => NULL,
        ),
        9 =>
        array (
          'name' =>
          array (
            'fa' => 'نساجی مازندران',
            'en' => 'FC Nassaji Mazandaran',
          ),
          'logo' => 'emb_0078_l.png',
          'city' =>
          array (
            'fa' => 'قائم‌شهر',
            'en' => 'Qaem Shahr',
          ),
          'pgl_team_id' => NULL,
        ),
        10 =>
        array (
          'name' =>
          array (
            'fa' => 'پیکان',
            'en' => 'Paykan FC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'تهران',
            'en' => 'Tehran',
          ),
          'pgl_team_id' => NULL,
        ),
        11 =>
        array (
          'name' =>
          array (
            'fa' => 'صنعت نفت آبادان',
            'en' => 'Sanat Naft FC',
          ),
          'logo' => 'emb_0120_l.png',
          'city' =>
          array (
            'fa' => 'آبادان',
            'en' => 'Abadan',
          ),
          'pgl_team_id' => NULL,
        ),
        12 =>
        array (
          'name' =>
          array (
            'fa' => 'فجر سپاسی شیراز',
            'en' => 'Fajr Sepasi Shiraz',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'شیراز',
            'en' => 'Shiraz',
          ),
          'pgl_team_id' => NULL,
        ),
        13 =>
        array (
          'name' =>
          array (
            'fa' => 'استقلال خوزستان',
            'en' => 'Esteghlal Khuzestan',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'اهواز',
            'en' => 'Ahvaz',
          ),
          'pgl_team_id' => NULL,
        ),
        14 =>
        array (
          'name' =>
          array (
            'fa' => 'شمس‌آذر قزوین',
            'en' => 'Shams Azar Qazvin',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'قزوین',
            'en' => 'Qazvin',
          ),
          'pgl_team_id' => NULL,
        ),
        15 =>
        array (
          'name' =>
          array (
            'fa' => 'خیبر خرم‌آباد',
            'en' => 'Kheybar Khorramabad FC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'خرم‌آباد',
            'en' => 'Khorramabad',
          ),
          'pgl_team_id' => NULL,
        ),
        16 =>
        array (
          'name' =>
          array (
            'fa' => 'چادرملو',
            'en' => 'Chadormalu SC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'یزد',
            'en' => 'Yazd',
          ),
          'pgl_team_id' => NULL,
        ),
        17 =>
        array (
          'name' =>
          array (
            'fa' => 'مس شهربابک',
            'en' => 'Mes Shahr Babak FC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'کرمان',
            'en' => 'Kerman',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
    'azadegan-league' =>
    array (
      'name' =>
      array (
        'fa' => 'لیگ آزادگان',
        'en' => 'Azadegan League',
      ),
      'country' =>
      array (
        'fa' => 'ایران',
        'en' => 'Iran',
      ),
      'tag' => 'IRN',
      'level' =>
      array (
        'fa' => 'سطح ۲ ایران',
        'en' => 'Iran · Tier 2',
      ),
      'teams_count' => 16,
      'logo' => NULL,
      'description' =>
      array (
        'fa' => 'لیگ آزادگان هم با پوشش کامل در پچ حاضر است؛ تیم‌ها، کیت‌ها و چهره‌های کلیدی این لیگ بازسازی شده‌اند تا مسیر صعود در حالت کاریر کاملاً واقعی باشد.',
        'en' => 'Iran\'s second tier is fully covered too — clubs, kits and key player faces are rebuilt so your career-mode promotion battle feels real.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'مس کرمان',
            'en' => 'Mes Kerman',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'کرمان',
            'en' => 'Kerman',
          ),
          'pgl_team_id' => NULL,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'سایپا',
            'en' => 'Saipa FC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'تهران',
            'en' => 'Tehran',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'مس رفسنجان',
            'en' => 'Mes Rafsanjan',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'رفسنجان',
            'en' => 'Rafsanjan',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'هوادار',
            'en' => 'Havadar SC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'اسلامشهر',
            'en' => 'Eslamshahr',
          ),
          'pgl_team_id' => NULL,
        ),
        4 =>
        array (
          'name' =>
          array (
            'fa' => 'پارس جنوبی جام',
            'en' => 'Pars Jonoubi Jam',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'جام',
            'en' => 'Jam',
          ),
          'pgl_team_id' => NULL,
        ),
        5 =>
        array (
          'name' =>
          array (
            'fa' => 'قشم',
            'en' => 'Qeshm Island CSC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'قشم',
            'en' => 'Qeshm',
          ),
          'pgl_team_id' => NULL,
        ),
        6 =>
        array (
          'name' =>
          array (
            'fa' => 'آریو اسلامشهر',
            'en' => 'Ario Eslamshahr',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'اسلامشهر',
            'en' => 'Eslamshahr',
          ),
          'pgl_team_id' => NULL,
        ),
        7 =>
        array (
          'name' =>
          array (
            'fa' => 'نیروی زمینی',
            'en' => 'Niroye Zamini FC',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'تهران',
            'en' => 'Tehran',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
    'premier-league' =>
    array (
      'name' =>
      array (
        'fa' => 'لیگ برتر انگلیس',
        'en' => 'Premier League',
      ),
      'country' =>
      array (
        'fa' => 'انگلیس',
        'en' => 'England',
      ),
      'tag' => 'ENG',
      'level' =>
      array (
        'fa' => 'سطح ۱ انگلیس',
        'en' => 'England · Tier 1',
      ),
      'teams_count' => 20,
      'logo' => 'images/landings/logos/emb/emb_0005_l.png',
      'description' =>
      array (
        'fa' => 'پرغلبه لیگ دنیا با کیت‌ها، چهره‌ها و استادیوم‌های به‌روز؛ جدول و برنامه مسابقات منطبق بر فصل جاری.',
        'en' => 'The world\'s most-watched league with updated kits, faces and stadiums, plus an authentic season calendar.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'منچستریونایتد',
            'en' => 'Manchester United',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'منچستر',
            'en' => 'Manchester',
          ),
          'pgl_team_id' => NULL,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'منچسترسیتی',
            'en' => 'Manchester City',
          ),
          'logo' => 'emb_0067_l.png',
          'city' =>
          array (
            'fa' => 'منچستر',
            'en' => 'Manchester',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'لیورپول',
            'en' => 'Liverpool',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'لیورپول',
            'en' => 'Liverpool',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'آرسنال',
            'en' => 'Arsenal',
          ),
          'logo' => 'emb_0074_l.png',
          'city' =>
          array (
            'fa' => 'لندن',
            'en' => 'London',
          ),
          'pgl_team_id' => NULL,
        ),
        4 =>
        array (
          'name' =>
          array (
            'fa' => 'چلسی',
            'en' => 'Chelsea',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'لندن',
            'en' => 'London',
          ),
          'pgl_team_id' => NULL,
        ),
        5 =>
        array (
          'name' =>
          array (
            'fa' => 'تاتنهام',
            'en' => 'Tottenham',
          ),
          'logo' => 'emb_0079_l.png',
          'city' =>
          array (
            'fa' => 'لندن',
            'en' => 'London',
          ),
          'pgl_team_id' => NULL,
        ),
        6 =>
        array (
          'name' =>
          array (
            'fa' => 'نیوکاسل',
            'en' => 'Newcastle United',
          ),
          'logo' => 'emb_0090_l.png',
          'city' =>
          array (
            'fa' => 'نیوکاسل',
            'en' => 'Newcastle',
          ),
          'pgl_team_id' => NULL,
        ),
        7 =>
        array (
          'name' =>
          array (
            'fa' => 'استون‌ویلا',
            'en' => 'Aston Villa',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'بیرمنگام',
            'en' => 'Birmingham',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
    'laliga' =>
    array (
      'name' =>
      array (
        'fa' => 'لالیگا',
        'en' => 'LaLiga',
      ),
      'country' =>
      array (
        'fa' => 'اسپانیا',
        'en' => 'Spain',
      ),
      'tag' => 'ESP',
      'level' =>
      array (
        'fa' => 'سطح ۱ اسپانیا',
        'en' => 'Spain · Tier 1',
      ),
      'teams_count' => 20,
      'logo' => 'images/landings/logos/emb/emb_0008_l.png',
      'description' =>
      array (
        'fa' => 'لالیگا با ال‌کلاسیکوی واقعی: کیت‌های فصل جدید، چهره‌های اسکن‌شده و استادیوم‌های اختصاصی رئال و بارسا.',
        'en' => 'LaLiga with a true El Clásico: this season\'s kits, scanned faces and dedicated home stadiums.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'رئال‌مادرید',
            'en' => 'Real Madrid',
          ),
          'detail' =>
          array (
            'stats' =>
            array (
              0 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره واقعی بازیکن',
                  'en' => 'Real player faces',
                ),
                'value' =>
                array (
                  'fa' => 'کامل ترکیب',
                  'en' => 'Full squad',
                ),
              ),
              1 =>
              array (
                'label' =>
                array (
                  'fa' => 'استادیوم واقعی',
                  'en' => 'Real stadium',
                ),
                'value' =>
                array (
                  'fa' => 'برنابئو — دارد',
                  'en' => 'Bernabéu — Yes',
                ),
              ),
              2 =>
              array (
                'label' =>
                array (
                  'fa' => 'چهره مربی',
                  'en' => 'Coach face',
                ),
                'value' =>
                array (
                  'fa' => 'دارد',
                  'en' => 'Yes',
                ),
              ),
            ),
            'tags' =>
            array (
              0 =>
              array (
                'fa' => 'کیت سفید کلاسیک',
                'en' => 'Classic white kit',
              ),
              1 =>
              array (
                'fa' => 'گیم‌پلی ال‌کلاسیکو',
                'en' => 'El Clásico gameplay',
              ),
            ),
          ),
          'logo' => 'emb_0049_l.png',
          'city' =>
          array (
            'fa' => 'مادرید',
            'en' => 'Madrid',
          ),
          'pgl_team_id' => NULL,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'بارسلونا',
            'en' => 'FC Barcelona',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'بارسلون',
            'en' => 'Barcelona',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'اتلتیکومادرید',
            'en' => 'Atlético Madrid',
          ),
          'logo' => 'emb_0056_l.png',
          'city' =>
          array (
            'fa' => 'مادرید',
            'en' => 'Madrid',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'سویا',
            'en' => 'Sevilla',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'سویا',
            'en' => 'Seville',
          ),
          'pgl_team_id' => NULL,
        ),
        4 =>
        array (
          'name' =>
          array (
            'fa' => 'والنسیا',
            'en' => 'Valencia',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'والنسیا',
            'en' => 'Valencia',
          ),
          'pgl_team_id' => NULL,
        ),
        5 =>
        array (
          'name' =>
          array (
            'fa' => 'بیلبائو',
            'en' => 'Athletic Club',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'بیلبائو',
            'en' => 'Bilbao',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
    'serie-a' =>
    array (
      'name' =>
      array (
        'fa' => 'سری آ',
        'en' => 'Serie A',
      ),
      'country' =>
      array (
        'fa' => 'ایتالیا',
        'en' => 'Italy',
      ),
      'tag' => 'ITA',
      'level' =>
      array (
        'fa' => 'سطح ۱ ایتالیا',
        'en' => 'Italy · Tier 1',
      ),
      'teams_count' => 20,
      'logo' => 'images/landings/logos/emb/emb_0009_l.png',
      'description' =>
      array (
        'fa' => 'کالچو با تاکتیک و گیم‌پلی سنگین؛ تیم‌های بزرگ ایتالیا با کیت و چهره‌های کامل.',
        'en' => 'Calcio with its trademark tactical weight — Italy\'s giants fully rebuilt.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'یوونتوس',
            'en' => 'Juventus',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'تورین',
            'en' => 'Turin',
          ),
          'pgl_team_id' => NULL,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'اینتر',
            'en' => 'Inter Milan',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'میلان',
            'en' => 'Milan',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'آث میلان',
            'en' => 'AC Milan',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'میلان',
            'en' => 'Milan',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'رم',
            'en' => 'AS Roma',
          ),
          'logo' => 'emb_0131_l.png',
          'city' =>
          array (
            'fa' => 'رم',
            'en' => 'Rome',
          ),
          'pgl_team_id' => NULL,
        ),
        4 =>
        array (
          'name' =>
          array (
            'fa' => 'ناپولی',
            'en' => 'Napoli',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'ناپولی',
            'en' => 'Naples',
          ),
          'pgl_team_id' => NULL,
        ),
        5 =>
        array (
          'name' =>
          array (
            'fa' => 'لاتزیو',
            'en' => 'Lazio',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'رم',
            'en' => 'Rome',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
    'bundesliga' =>
    array (
      'name' =>
      array (
        'fa' => 'بوندسلیگا',
        'en' => 'Bundesliga',
      ),
      'country' =>
      array (
        'fa' => 'آلمان',
        'en' => 'Germany',
      ),
      'tag' => 'GER',
      'level' =>
      array (
        'fa' => 'سطح ۱ آلمان',
        'en' => 'Germany · Tier 1',
      ),
      'teams_count' => 18,
      'logo' => 'images/landings/logos/emb/emb_0009_l.png',
      'description' =>
      array (
        'fa' => 'بوندسلیگای پرهیجان با فوتبال تهاجمی و استادیوم‌های پرمخاطب.',
        'en' => 'High-tempo Bundesliga football with its famous full houses.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'بایرن‌مونیخ',
            'en' => 'Bayern München',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'مونیخ',
            'en' => 'Munich',
          ),
          'pgl_team_id' => NULL,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'دورتموند',
            'en' => 'Borussia Dortmund',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'دورتموند',
            'en' => 'Dortmund',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'لورکوزن',
            'en' => 'Bayer Leverkusen',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'لورکوزن',
            'en' => 'Leverkusen',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'لایپزیگ',
            'en' => 'RB Leipzig',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'لایپزیگ',
            'en' => 'Leipzig',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
    'ligue-1' =>
    array (
      'name' =>
      array (
        'fa' => 'لیگ ۱ فرانسه',
        'en' => 'Ligue 1',
      ),
      'country' =>
      array (
        'fa' => 'فرانسه',
        'en' => 'France',
      ),
      'tag' => 'FRA',
      'level' =>
      array (
        'fa' => 'سطح ۱ فرانسه',
        'en' => 'France · Tier 1',
      ),
      'teams_count' => 18,
      'logo' => NULL,
      'description' =>
      array (
        'fa' => 'لیگ ۱ با ستاره‌های بزرگ و فوتبال فنی؛ پاریس در قلب آن.',
        'en' => 'Ligue 1 with its stars and technical football — Paris at the heart.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'پاری‌سن‌ژرمن',
            'en' => 'Paris Saint-Germain',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'پاریس',
            'en' => 'Paris',
          ),
          'pgl_team_id' => NULL,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'مارسی',
            'en' => 'Olympique Marseille',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'مارسی',
            'en' => 'Marseille',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'لیون',
            'en' => 'Olympique Lyon',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'لیون',
            'en' => 'Lyon',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'موناکو',
            'en' => 'AS Monaco',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'موناکو',
            'en' => 'Monaco',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
    'uefa-champions-league' =>
    array (
      'name' =>
      array (
        'fa' => 'لیگ قهرمانان اروپا',
        'en' => 'UEFA Champions League',
      ),
      'country' =>
      array (
        'fa' => 'اروپا',
        'en' => 'Europe',
      ),
      'tag' => 'UCL',
      'level' =>
      array (
        'fa' => 'جام باشگاه‌های اروپا',
        'en' => 'Continental Cup',
      ),
      'teams_count' => 36,
      'logo' => 'images/landings/logos/emb/emb_0001_l.png',
      'description' =>
      array (
        'fa' => 'شب‌های جادویی اروپا با جام، موسیقی ورود و گرافیک اختصاصی پخش؛ تجربه واقعی UCL از گروهی تا فینال.',
        'en' => 'European nights with the iconic trophy, anthem-style presentation and dedicated broadcast graphics from group stage to the final.',
      ),
      'teams' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'رئال‌مادرید',
            'en' => 'Real Madrid',
          ),
          'logo' => 'emb_0049_l.png',
          'city' =>
          array (
            'fa' => 'اسپانیا',
            'en' => 'Spain',
          ),
          'pgl_team_id' => NULL,
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'مانچسترسیتی',
            'en' => 'Manchester City',
          ),
          'logo' => 'emb_0067_l.png',
          'city' =>
          array (
            'fa' => 'انگلیس',
            'en' => 'England',
          ),
          'pgl_team_id' => NULL,
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'بایرن‌مونیخ',
            'en' => 'Bayern München',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'آلمان',
            'en' => 'Germany',
          ),
          'pgl_team_id' => NULL,
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'پاری‌سن‌ژرمن',
            'en' => 'Paris Saint-Germain',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'فرانسه',
            'en' => 'France',
          ),
          'pgl_team_id' => NULL,
        ),
        4 =>
        array (
          'name' =>
          array (
            'fa' => 'اینتر',
            'en' => 'Inter Milan',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'ایتالیا',
            'en' => 'Italy',
          ),
          'pgl_team_id' => NULL,
        ),
        5 =>
        array (
          'name' =>
          array (
            'fa' => 'بارسلونا',
            'en' => 'FC Barcelona',
          ),
          'logo' => NULL,
          'city' =>
          array (
            'fa' => 'اسپانیا',
            'en' => 'Spain',
          ),
          'pgl_team_id' => NULL,
        ),
      ),
    ),
  ),
  'globe' =>
  array (
    0 =>
    array (
      'slug' => 'persian-gulf-pro-league',
      'countryKey' => 'Iran',
      'lat' => 35.5,
      'lng' => 53.5,
      'name' =>
      array (
        'fa' => 'ایران — لیگ برتر خلیج فارس',
        'en' => 'Iran — Persian Gulf Pro League',
      ),
      'country' =>
      array (
        'fa' => 'ایران',
        'en' => 'Iran',
      ),
      'regions' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'تهران',
            'en' => 'Tehran',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'پرسپولیس',
                'en' => 'Persepolis FC',
              ),
              'lat' => 35.69,
              'lng' => 51.37,
              'city' =>
              array (
                'fa' => 'تهران',
                'en' => 'Tehran',
              ),
              'logo' => NULL,
            ),
            1 =>
            array (
              'name' =>
              array (
                'fa' => 'استقلال',
                'en' => 'Esteghlal FC',
              ),
              'lat' => 35.71,
              'lng' => 51.43,
              'city' =>
              array (
                'fa' => 'تهران',
                'en' => 'Tehran',
              ),
              'logo' => 'emb_0112_l.png',
            ),
            2 =>
            array (
              'name' =>
              array (
                'fa' => 'پیکان',
                'en' => 'Paykan FC',
              ),
              'lat' => 35.76,
              'lng' => 51.5,
              'city' =>
              array (
                'fa' => 'تهران',
                'en' => 'Tehran',
              ),
              'logo' => NULL,
            ),
          ),
        ),
        1 =>
        array (
          'name' =>
          array (
            'fa' => 'اصفهان',
            'en' => 'Isfahan',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'سپاهان',
                'en' => 'Sepahan FC',
              ),
              'lat' => 32.62,
              'lng' => 51.63,
              'city' =>
              array (
                'fa' => 'اصفهان',
                'en' => 'Isfahan',
              ),
              'logo' => 'emb_0084_l.png',
            ),
            1 =>
            array (
              'name' =>
              array (
                'fa' => 'ذوب‌آهن',
                'en' => 'Zob Ahan Esfahan',
              ),
              'lat' => 32.72,
              'lng' => 51.72,
              'city' =>
              array (
                'fa' => 'اصفهان',
                'en' => 'Isfahan',
              ),
              'logo' => NULL,
            ),
          ),
        ),
        2 =>
        array (
          'name' =>
          array (
            'fa' => 'آذربایجان شرقی',
            'en' => 'East Azerbaijan',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'تراکتور',
                'en' => 'Tractor FC',
              ),
              'lat' => 38.05,
              'lng' => 46.29,
              'city' =>
              array (
                'fa' => 'تبریز',
                'en' => 'Tabriz',
              ),
              'logo' => 'emb_0111_l.png',
            ),
          ),
        ),
        3 =>
        array (
          'name' =>
          array (
            'fa' => 'خوزستان',
            'en' => 'Khuzestan',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'فولاد',
                'en' => 'Foolad FC',
              ),
              'lat' => 31.32,
              'lng' => 48.67,
              'city' =>
              array (
                'fa' => 'اهواز',
                'en' => 'Ahvaz',
              ),
              'logo' => 'emb_0047_l.png',
            ),
          ),
        ),
        4 =>
        array (
          'name' =>
          array (
            'fa' => 'مازندران',
            'en' => 'Mazandaran',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'نساجی',
                'en' => 'FC Nassaji Mazandaran',
              ),
              'lat' => 36.47,
              'lng' => 52.86,
              'city' =>
              array (
                'fa' => 'قائم‌شهر',
                'en' => 'Qaem Shahr',
              ),
              'logo' => 'emb_0078_l.png',
            ),
          ),
        ),
        5 =>
        array (
          'name' =>
          array (
            'fa' => 'گیلان',
            'en' => 'Gilan',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'ملوان',
                'en' => 'Malavan Bandar Anzali',
              ),
              'lat' => 37.47,
              'lng' => 49.46,
              'city' =>
              array (
                'fa' => 'بندر انزلی',
                'en' => 'Bandar Anzali',
              ),
              'logo' => 'emb_0086_l.png',
            ),
          ),
        ),
      ),
    ),
    1 =>
    array (
      'slug' => 'premier-league',
      'countryKey' => 'United Kingdom',
      'lat' => 52.6,
      'lng' => -1.6,
      'name' =>
      array (
        'fa' => 'انگلیس — لیگ برتر',
        'en' => 'England — Premier League',
      ),
      'country' =>
      array (
        'fa' => 'انگلیس',
        'en' => 'England',
      ),
      'regions' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'انگلیس',
            'en' => 'England',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'منچسترسیتی',
                'en' => 'Manchester City',
              ),
              'lat' => 53.48,
              'lng' => -2.24,
              'city' =>
              array (
                'fa' => 'منچستر',
                'en' => 'Manchester',
              ),
              'logo' => 'emb_0067_l.png',
            ),
            1 =>
            array (
              'name' =>
              array (
                'fa' => 'آرسنال',
                'en' => 'Arsenal',
              ),
              'lat' => 51.55,
              'lng' => -0.08,
              'city' =>
              array (
                'fa' => 'لندن',
                'en' => 'London',
              ),
              'logo' => 'emb_0074_l.png',
            ),
            2 =>
            array (
              'name' =>
              array (
                'fa' => 'تاتنهام',
                'en' => 'Tottenham',
              ),
              'lat' => 51.6,
              'lng' => -0.03,
              'city' =>
              array (
                'fa' => 'لندن',
                'en' => 'London',
              ),
              'logo' => 'emb_0079_l.png',
            ),
            3 =>
            array (
              'name' =>
              array (
                'fa' => 'نیوکاسل',
                'en' => 'Newcastle United',
              ),
              'lat' => 54.98,
              'lng' => -1.62,
              'city' =>
              array (
                'fa' => 'نیوکاسل',
                'en' => 'Newcastle United',
              ),
              'logo' => 'emb_0090_l.png',
            ),
          ),
        ),
      ),
    ),
    2 =>
    array (
      'slug' => 'laliga',
      'countryKey' => 'Spain',
      'lat' => 40.2,
      'lng' => -3.7,
      'name' =>
      array (
        'fa' => 'اسپانیا — لالیگا',
        'en' => 'Spain — LaLiga',
      ),
      'country' =>
      array (
        'fa' => 'اسپانیا',
        'en' => 'Spain',
      ),
      'regions' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'اسپانیا',
            'en' => 'Spain',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'رئال‌مادرید',
                'en' => 'Real Madrid',
              ),
              'lat' => 40.45,
              'lng' => -3.69,
              'city' =>
              array (
                'fa' => 'مادرید',
                'en' => 'Madrid',
              ),
              'logo' => 'emb_0049_l.png',
            ),
            1 =>
            array (
              'name' =>
              array (
                'fa' => 'اتلتیکومادرید',
                'en' => 'Atletico Madrid',
              ),
              'lat' => 40.43,
              'lng' => -3.55,
              'city' =>
              array (
                'fa' => 'مادرید',
                'en' => 'Madrid',
              ),
              'logo' => 'emb_0056_l.png',
            ),
          ),
        ),
      ),
    ),
    3 =>
    array (
      'slug' => 'serie-a',
      'countryKey' => 'Italy',
      'lat' => 42.8,
      'lng' => 12.5,
      'name' =>
      array (
        'fa' => 'ایتالیا — سری آ',
        'en' => 'Italy — Serie A',
      ),
      'country' =>
      array (
        'fa' => 'ایتالیا',
        'en' => 'Italy',
      ),
      'regions' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'ایتالیا',
            'en' => 'Italy',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'رم',
                'en' => 'AS Roma',
              ),
              'lat' => 41.89,
              'lng' => 12.49,
              'city' =>
              array (
                'fa' => 'رم',
                'en' => 'Rome',
              ),
              'logo' => 'emb_0131_l.png',
            ),
          ),
        ),
      ),
    ),
    4 =>
    array (
      'slug' => 'bundesliga',
      'countryKey' => 'Germany',
      'lat' => 51.1,
      'lng' => 10.4,
      'name' =>
      array (
        'fa' => 'آلمان — بوندسلیگا',
        'en' => 'Germany — Bundesliga',
      ),
      'country' =>
      array (
        'fa' => 'آلمان',
        'en' => 'Germany',
      ),
      'regions' =>
      array (
        0 =>
        array (
          'name' =>
          array (
            'fa' => 'آلمان',
            'en' => 'Germany',
          ),
          'teams' =>
          array (
            0 =>
            array (
              'name' =>
              array (
                'fa' => 'بایرن‌مونیخ',
                'en' => 'Bayern Munchen',
              ),
              'lat' => 48.14,
              'lng' => 11.58,
              'city' =>
              array (
                'fa' => 'مونیخ',
                'en' => 'Munich',
              ),
              'logo' => NULL,
            ),
            1 =>
            array (
              'name' =>
              array (
                'fa' => 'دورتموند',
                'en' => 'Borussia Dortmund',
              ),
              'lat' => 51.51,
              'lng' => 7.46,
              'city' =>
              array (
                'fa' => 'دورتموند',
                'en' => 'Borussia Dortmund',
              ),
              'logo' => NULL,
            ),
          ),
        ),
      ),
    ),
  ),
  'stadiums' =>
  array (
    0 =>
    array (
      'name' =>
      array (
        'fa' => 'آزادی',
        'en' => 'Azadi Stadium',
      ),
      'city' =>
      array (
        'fa' => 'تهران',
        'en' => 'Tehran',
      ),
      'capacity' => 78116,
      'team' =>
      array (
        'fa' => 'پرسپولیس و استقلال',
        'en' => 'Persepolis & Esteghlal',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    1 =>
    array (
      'name' =>
      array (
        'fa' => 'نقش جهان',
        'en' => 'Naqsh-e Jahan Stadium',
      ),
      'city' =>
      array (
        'fa' => 'اصفهان',
        'en' => 'Isfahan',
      ),
      'capacity' => 75000,
      'team' =>
      array (
        'fa' => 'سپاهان',
        'en' => 'Sepahan',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    2 =>
    array (
      'name' =>
      array (
        'fa' => 'سهند',
        'en' => 'Sahand Stadium',
      ),
      'city' =>
      array (
        'fa' => 'تبریز',
        'en' => 'Tabriz',
      ),
      'capacity' => 68000,
      'team' =>
      array (
        'fa' => 'تراکتور',
        'en' => 'Tractor',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    3 =>
    array (
      'name' =>
      array (
        'fa' => 'غدیر',
        'en' => 'Ghadir Stadium',
      ),
      'city' =>
      array (
        'fa' => 'اهواز',
        'en' => 'Ahvaz',
      ),
      'capacity' => 38000,
      'team' =>
      array (
        'fa' => 'فولاد و استقلال خوزستان',
        'en' => 'Foolad & Est. Khuzestan',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    4 =>
    array (
      'name' =>
      array (
        'fa' => 'ملوان',
        'en' => 'Malavan Stadium',
      ),
      'city' =>
      array (
        'fa' => 'بندر انزلی',
        'en' => 'Bandar Anzali',
      ),
      'capacity' => 12000,
      'team' =>
      array (
        'fa' => 'ملوان',
        'en' => 'Malavan',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    5 =>
    array (
      'name' =>
      array (
        'fa' => 'گل‌گهر',
        'en' => 'Gol Gohar Stadium',
      ),
      'city' =>
      array (
        'fa' => 'سیرجان',
        'en' => 'Sirjan',
      ),
      'capacity' => 15000,
      'team' =>
      array (
        'fa' => 'گل‌گهر',
        'en' => 'Gol Gohar',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    6 =>
    array (
      'name' =>
      array (
        'fa' => 'ونبل',
        'en' => 'Wembley',
      ),
      'city' =>
      array (
        'fa' => 'لندن',
        'en' => 'London',
      ),
      'capacity' => 90000,
      'team' =>
      array (
        'fa' => 'تیم ملی انگلیس',
        'en' => 'England NT',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    7 =>
    array (
      'name' =>
      array (
        'fa' => 'سن‌سیرو',
        'en' => 'San Siro',
      ),
      'city' =>
      array (
        'fa' => 'میلان',
        'en' => 'Milan',
      ),
      'capacity' => 80018,
      'team' =>
      array (
        'fa' => 'میلان و اینتر',
        'en' => 'Milan & Inter',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    8 =>
    array (
      'name' =>
      array (
        'fa' => 'آلیانزآرنا',
        'en' => 'Allianz Arena',
      ),
      'city' =>
      array (
        'fa' => 'مونیخ',
        'en' => 'Munich',
      ),
      'capacity' => 75000,
      'team' =>
      array (
        'fa' => 'بایرن‌مونیخ',
        'en' => 'Bayern',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
    9 =>
    array (
      'name' =>
      array (
        'fa' => 'کامپ‌نو',
        'en' => 'Camp Nou',
      ),
      'city' =>
      array (
        'fa' => 'بارسلون',
        'en' => 'Barcelona',
      ),
      'capacity' => 99354,
      'team' =>
      array (
        'fa' => 'بارسلونا',
        'en' => 'FC Barcelona',
      ),
      'image' => 'images/landings/shared/g3-stadium.svg',
    ),
  ),
  'trophies' =>
  array (
    0 =>
    array (
      'name' =>
      array (
        'fa' => 'جام لیگ برتر خلیج فارس',
        'en' => 'Persian Gulf Pro League Trophy',
      ),
      'competition' =>
      array (
        'fa' => 'ایران',
        'en' => 'Iran',
      ),
      'detail' =>
      array (
        'fa' => 'مدل سه‌بعدی اختصاصی جام لیگ برتر با مراسم اهدای ایرانی',
        'en' => 'Custom 3D model with an authentic Iranian celebration scene',
      ),
    ),
    1 =>
    array (
      'name' =>
      array (
        'fa' => 'جام حذفی ایران',
        'en' => 'Hazfi Cup',
      ),
      'competition' =>
      array (
        'fa' => 'ایران',
        'en' => 'Iran',
      ),
      'detail' =>
      array (
        'fa' => 'جام حذفی با مسیر کامل از مرحله اول',
        'en' => 'Full knockout path from round one',
      ),
    ),
    2 =>
    array (
      'name' =>
      array (
        'fa' => 'سوپرجام ایران',
        'en' => 'Iranian Super Cup',
      ),
      'competition' =>
      array (
        'fa' => 'ایران',
        'en' => 'Iran',
      ),
      'detail' =>
      array (
        'fa' => 'دیدگاه قهرمان لیگ و جام حذفی',
        'en' => 'Champions of league vs cup',
      ),
    ),
    3 =>
    array (
      'name' =>
      array (
        'fa' => 'جام قهرمانان اروپا',
        'en' => 'UEFA Champions League Trophy',
      ),
      'competition' =>
      array (
        'fa' => 'اروپا',
        'en' => 'Europe',
      ),
      'detail' =>
      array (
        'fa' => 'جام معروف UCL با «بیگ‌ایار» واقعی',
        'en' => 'The iconic big-eared cup',
      ),
    ),
    4 =>
    array (
      'name' =>
      array (
        'fa' => 'جام لیگ اروپا',
        'en' => 'UEFA Europa League Trophy',
      ),
      'competition' =>
      array (
        'fa' => 'اروپا',
        'en' => 'Europe',
      ),
      'detail' =>
      array (
        'fa' => 'جام بدون دسته لیگ اروپا',
        'en' => 'The handleless UEL cup',
      ),
    ),
    5 =>
    array (
      'name' =>
      array (
        'fa' => 'جام لیگ برتر انگلیس',
        'en' => 'Premier League Trophy',
      ),
      'competition' =>
      array (
        'fa' => 'انگلیس',
        'en' => 'England',
      ),
      'detail' =>
      array (
        'fa' => 'جام طلایی پریمیرلیگ با روبان',
        'en' => 'The golden crown jewel',
      ),
    ),
    6 =>
    array (
      'name' =>
      array (
        'fa' => 'جام لالیگا',
        'en' => 'LaLiga Trophy',
      ),
      'competition' =>
      array (
        'fa' => 'اسپانیا',
        'en' => 'Spain',
      ),
      'detail' =>
      array (
        'fa' => 'جام سبز رنگ لالیگا',
        'en' => 'The green LFP trophy',
      ),
    ),
    7 =>
    array (
      'name' =>
      array (
        'fa' => 'جام بوندسلیگا',
        'en' => 'Bundesliga Trophy',
      ),
      'competition' =>
      array (
        'fa' => 'آلمان',
        'en' => 'Germany',
      ),
      'detail' =>
      array (
        'fa' => 'کاسه معروف نقره‌ای بوندسلیگا',
        'en' => 'The legendary silver salad bowl',
      ),
    ),
  ),
);
