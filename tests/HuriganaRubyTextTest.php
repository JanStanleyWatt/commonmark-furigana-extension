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

namespace JSW\Tests;

use JSW\Hurigana\HuriganaExtension;
use JSW\Hurigana\Node\RubyText;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Document;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \JSW\Hurigana\Delimiter\RubyTextDelimiterProcesser
 *
 * @group rubytext
 */
final class HuriganaRubyTextTest extends TestCase
{
    private const DEFAULT_RULE = [
        'hurigana' => [
            'use_sutegana' => false,
            'use_rp_tag' => false,
        ],
    ];

    private function makeExpect(string $expect)
    {
        return '<p>'.$expect.'</p>'."\n";
    }

    private function digData(Document $document): int
    {
        $answer = -1;
        foreach ($document->iterator() as $node) {
            if ($node instanceof RubyText) {
                return $node->data->get('text_length', -1);
            }
        }
    }

    /**
     * @covers \JSW\Hurigana\Parser\RubyTextParser::getMatchDefinition
     * @covers \JSW\Hurigana\Parser\RubyTextParser::parse
     */
    public function testSimpleRubyText(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new HuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rt>かくちょうきのう</rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers :: process
     */
    public function testEmphasisInRubyText(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new HuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rt><strong>かくちょうきのう</strong></rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《**かくちょうきのう**》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers :: digRubyText
     */
    public function testCountToLengthRubyText(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new HuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = 8;

        $document_1 = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getDocument();
        $actual_1 = $this->digData($document_1);

        $document_2 = $converter->convert('この拡張機能《***かくちょうきのう***》は素晴らしい')->getDocument();
        $actual_2 = $this->digData($document_2);

        $document_3 = $converter->convert('この拡張機能《`かくちょうきのう`》は素晴らしい')->getDocument();
        $actual_3 = $this->digData($document_3);

        $document_4 = $converter->convert('この拡張機能《**かくちょう*きのう***》は素晴らしい')->getDocument();
        $actual_4 = $this->digData($document_4);

        $this->assertSame($expect, $actual_1, 'Failed simple pattern');
        $this->assertSame($expect, $actual_2, 'Failed nested pattern');
        $this->assertSame($expect, $actual_3, 'Failed branching pattern');
        $this->assertSame($expect, $actual_4, 'Failed <code> pattern');
    }

    /**
     * @covers ::sutegana
     */
    public function testSutegana(): void
    {
        $environment = new Environment([
            'hurigana' => [
                'use_sutegana' => true,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new HuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect_1 = $this->makeExpect('この<ruby>拡張機能<rt>かくちようきのう</rt></ruby>は素晴らしい');
        $actual_1 = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getContent();
        $this->assertSame($expect_1, $actual_1, 'Failed hiragana');

        $expect_2 = $this->makeExpect('この<ruby>拡張機能<rt>エクステンシヨン</rt></ruby>は素晴らしい');
        $actual_2 = $converter->convert('この拡張機能《エクステンション》は素晴らしい')->getContent();
        $this->assertSame($expect_2, $actual_2, 'Failed katakana');
    }
}
