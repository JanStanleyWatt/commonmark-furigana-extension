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
use League\CommonMark\Delimiter\Delimiter;
use League\CommonMark\Environment\EnvironmentAwareInterface;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

final class SapphireCloseParser implements InlineParserInterface, EnvironmentAwareInterface
{
    private EnvironmentInterface $environment;

    public function setEnvironment(EnvironmentInterface $environment): void
    {
        $this->environment = $environment;
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex('《.*?(?=》)');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $container = $inlineContext->getContainer();

        $opener = $inlineContext->getDelimiterStack()->searchByCharacter('｜');
        if (null === $opener) {
            return false;
        }
        if (!$opener->isActive()) {
            $inlineContext->getDelimiterStack()->removeDelimiter($opener);

            return false;
        }

        // <rt>タグ登録
        $cursor->advance();
        $ruby = $cursor->match('/.*?(?=》)/u') ?? '';
        $container->appendChild(new RTNode($ruby));

        // <ruby>タグの閉め区切り文字「》」を挿入し、同時に内部の区切り文字の処理を清算する
        $cursor->advance();
        $node = new Text('》', ['delim' => true]);
        $container->appendChild($node);
        $delimiter = new Delimiter('》', 1, $node, false, true);
        $inlineContext->getDelimiterStack()->push($delimiter);

        $stack = $inlineContext->getDelimiterStack();
        $bottom = $opener->getPrevious();
        $stack->processDelimiters($bottom, $this->environment->getDelimiterProcessors());
        $stack->removeAll($bottom);

        return true;
    }
}
