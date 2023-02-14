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

namespace JSW\Sapphire\Parser;

use JSW\Sapphire\Node\RTNode;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

final class SapphireOpenParser implements InlineParserInterface
{
    private string $regex_pattern;

    public function __construct(string $pattern)
    {
        $this->regex_pattern = $pattern.'(?=《)';
    }

    /**
     * 外部のルビ区切りパターンを使ってフックさせる。
     * パターン変更を容易にするとともに、
     * 配列を使って複数パターンを一括登録させることが出来る。
     */
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex($this->regex_pattern);
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $container = $inlineContext->getContainer();
        $state = $cursor->saveState();

        // 不正な構文を弾く
        if ($cursor->isAtEnd() || 0 === $cursor->getPosition()
        || '｜' === $cursor->peek(-1)) {
            return false;
        }

        // 親文字を抽出
        $cursor->match('/'.$this->regex_pattern.'/u');

        /**
         * 対応する閉じかっこ（》）がない場合、他の開きかっこ（《）があった場合は
         * restoreしてfalseを返す。
         */
        $will = $cursor->getRemainder();
        $kakko_match = preg_match('/.*?《/u', $will, $unused, 0, strlen('《'));
        if ($kakko_match) {
            $cursor->restoreState($state);

            return false;
        }

        // 区切り文字を入れた親文字を登録
        $parent = '｜'.$cursor->getPreviousText();
        $container->appendChild(new Text($parent, ['delim' => true]));

        // <rt>タグを挿入
        $cursor->advance();
        $cursor->match('/.*?(?=》)/u');
        $container->appendChild(new RTNode($cursor->getPreviousText()));

        return true;
    }
}
