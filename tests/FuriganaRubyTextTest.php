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

use JSW\Furigana\FuriganaExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass('\JSW\Furigana\Delimiter\RubyTextDelimiterProcesser')]
#[CoversFunction('\JSW\Furigana\Parser\RubyTextParser::getMatchDefinition')]
#[CoversFunction('\JSW\Furigana\Parser\RubyTextParser::parse')]
#[CoversFunction('::process')]
#[CoversFunction('::sutegana')]
#[Group('rubytext')]
final class FuriganaRubyTextTest extends TestCase
{
    private const DEFAULT_RULE = [
        'furigana' => [
            'use_sutegana' => false,
            'use_rp_tag' => false,
        ],
    ];

    private function makeExpect(string $expect)
    {
        return '<p>'.$expect.'</p>'."\n";
    }

    #[Test]
    public function testSimpleRubyText(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rt>かくちょうきのう</rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    #[Test]
    public function testEmphasisInRubyText(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rt><strong>かくちょうきのう</strong></rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《**かくちょうきのう**》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    #[Test]
    public function testSutegana(): void
    {
        $environment = new Environment([
            'furigana' => [
                'use_sutegana' => true,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect_1 = $this->makeExpect('この<ruby>拡張機能<rt>かくちようきのう</rt></ruby>は素晴らしい');
        $actual_1 = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getContent();
        $this->assertSame($expect_1, $actual_1, 'Failed hiragana');

        $expect_2 = $this->makeExpect('この<ruby>拡張機能<rt>エクステンシヨン</rt></ruby>は素晴らしい');
        $actual_2 = $converter->convert('この拡張機能《エクステンション》は素晴らしい')->getContent();
        $this->assertSame($expect_2, $actual_2, 'Failed katakana');
    }
}
