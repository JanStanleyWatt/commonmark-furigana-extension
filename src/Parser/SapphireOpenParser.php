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

use League\CommonMark\Delimiter\Delimiter;
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

        // 区切り文字スタックに既に開き区切り文字「｜」がある場合は処理を飛ばす
        if (null !== $inlineContext->getDelimiterStack()->searchByCharacter('｜')) {
            return false;
        }

        if ('｜' === $cursor->getCurrentCharacter()) {
            $inlineContext->getCursor()->advance();
        }

        $node = new Text('｜', ['delim' => true]);
        $container->appendChild($node);

        $delimiter = new Delimiter('｜', 1, $node, true, false, $inlineContext->getCursor()->getPosition());
        $inlineContext->getDelimiterStack()->push($delimiter);

        return true;
    }
}
