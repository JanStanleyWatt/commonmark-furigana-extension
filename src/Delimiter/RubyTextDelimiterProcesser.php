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

namespace JSW\Hurigana\Delimiter;

use JSW\Hurigana\Node\RubyText;
use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Delimiter\Processor\DelimiterProcessorInterface;
use League\CommonMark\Node\Inline\AbstractStringContainer;
use League\CommonMark\Node\Node;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

final class RubyTextDelimiterProcesser implements DelimiterProcessorInterface, ConfigurationAwareInterface
{
    private ConfigurationInterface $config;
    private int $char_length = 0;

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

    /**
     * @TODO: #16 深度制限を実装すべきか検討する
     */
    private function digRubyText(Node $ruby_node)
    {
        $ruby_node->data->set('is_digged', true);

        // 深さ優先検索でルビ文字を見つける
        foreach ($ruby_node->iterator() as $node) {
            if ($node->data->get('is_digged', false)) {
                continue;
            }
            if ($node->hasChildren()) {
                $this->digRubyText($node);
            } elseif ($node instanceof AbstractStringContainer) {
                $node->data->set('is_digged', true);
                $tmp = $node->getLiteral();
                $this->char_length += mb_strlen($tmp);

                if ($this->config->get('hurigana/use_sutegana')) {
                    $node->setLiteral($this->sutegana($tmp));
                }
            }
        }
    }

    /**
     * @return int:\<rt\>から\<\/rt\>までのルビ文字の文字数。
     */
    public function rubyTextLength(): int
    {
        return $this->char_length;
    }

    public function process(AbstractStringContainer $opener, AbstractStringContainer $closer, int $delimiterUse): void
    {
        $node = new RubyText();
        $this->char_length = 0;

        $next = $opener->next();
        while (null !== $next && $next !== $closer) {
            $tmp = $next->next();
            $node->appendChild($next);
            $next = $tmp;
        }

        $this->digRubyText($node);
        $node->data->set('text_length',$this->char_length);
        $opener->insertAfter($node);
    }

    public function getOpeningCharacter(): string
    {
        return '《';
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
