<?php

/**
 * Copyright 2023 Jan Stanley Watt

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 *  http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace JSW\Furigana\Util;

class FuriganaKugiri
{
    /**
     * 親文字の区切りパターンを収めた配列。
     */
    private $kugiri = [
        // 区切り文字（｜）
        'delimited' => '\\?｜([^｜]*?)',
        // 漢字
        'kanji' => '\p{Ideographic}[ヵヶゕ\p{Ideographic}]*?',
        // 全角英数
        'zenkaku_alphanum' => '[Ａ-Ｚａ-ｚ０-９\p{Greek}\p{Cyrillic}―＆’，．－]*?',
        // 半角英数(実質ASCII文字)
        'hankaku_alphanum' => '[[:ascii:]]([[:ascii:]]|…)*?',
        // カタカナ(イコールっぽく見える記号はダブルハイフンと言う別物)
        'zenkaku_katakana' => '\p{Katakana}[ﾞﾟ゛゜゠\-ー・ヽヾ\p{Katakana}]*?',
        // ひらがな
        'hiragana' => '[あ-ゖ][ﾞﾟ゛゜・ーゝゞ\p{Hiragana}]*?',
    ];

    public function getKugiri(): array
    {
        return $this->kugiri;
    }
}
