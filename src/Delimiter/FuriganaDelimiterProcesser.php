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

namespace JSW\Furigana\Delimiter;

use JSW\Furigana\Node\Ruby;
use JSW\Furigana\Node\RubyParentheses;
use JSW\Furigana\Node\RubyText;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

/**
 * TODO: #17 モノルビ機能を実装できないか試してみる.
 */
class FuriganaDelimiterProcesser implements DelimiterProcessorInterface, ConfigurationAwareInterface
{
    private ConfigurationInterface $config;

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    /**
     * 捨て仮名を大文字に置換する関数。
     * 引数にある置換フラグは実際にはプロパティで持たせる。
     * 捨て仮名コードは以下の505行目から511行目までよりお借りしました。
     *
     * @see https://github.com/noisan/parsedown-rubytext/blob/master/lib/Parsedown/RubyTextTrait.php
     *
     * Copyright (c) 2015 Akihiro Yamanoi
     * Released under the MIT license
     * https://raw.githubusercontent.com/noisan/parsedown-rubytext/master/LICENSE
     */
    private function sutegana(string $ruby): string
    {
        $ruby_text_Sutegana = [
            // 小書き文字をfromに、並字をtoに置く。ペアの要素順は合わせること
            'from' => ['ぁ', 'ぃ', 'ぅ', 'ぇ', 'ぉ', 'っ', 'ゃ', 'ゅ', 'ょ', 'ゎ', 'ァ', 'ィ', 'ゥ', 'ェ', 'ォ', 'ヵ', 'ヶ', 'ッ', 'ャ', 'ュ', 'ョ', 'ヮ'],
            'to' => ['あ', 'い', 'う', 'え', 'お', 'つ', 'や', 'ゆ', 'よ', 'わ', 'ア', 'イ', 'ウ', 'エ', 'オ', 'カ', 'ケ', 'ツ', 'ヤ', 'ユ', 'ヨ', 'ワ'],
            // 小さいクなどは文字化けしてしまった
        ];

        return str_replace($ruby_text_Sutegana['from'], $ruby_text_Sutegana['to'], $ruby);
    }

    public function process(AbstractStringContainer $opener, AbstractStringContainer $closer, int $delimiterUse): void
    {
        $node = new Ruby();

        $next = $opener->next();
        while (null !== $next && !$next instanceof RubyText) {
            $tmp = $next->next();
            $node->appendChild($next);
            $next = $tmp;
        }

        $rt = $next;
        $next = $next->next();
        $use_sutegana = $this->config->get('furigana/use_sutegana');
        while (null !== $next && $next !== $closer) {
            $tmp = $next->next();

            if ($use_sutegana && $next instanceof AbstractStringContainer) {
                $next->setLiteral($this->sutegana($next->getLiteral()));
            }

            $rt->appendChild($next);
            $next = $tmp;
        }

        $node->appendChild($rt);

        $use_rp_tag = $this->config->get('furigana/use_rp_tag');
        foreach ($node->iterator() as $it) {
            if ($use_rp_tag && $it instanceof RubyText) {
                $it->insertBefore(new RubyParentheses('('));
                $it->insertAfter(new RubyParentheses(')'));
            }
        }

        $opener->insertAfter($node);
    }

    public function getOpeningCharacter(): string
    {
        return '｜';
    }

    public function getClosingCharacter(): string
    {
        return '》';
    }

    public function getMinLength(): int
    {
        return 1;
    }

    public function getDelimiterUse(DelimiterInterface $opener, DelimiterInterface $closer): int
    {
        return 1;
    }
}
