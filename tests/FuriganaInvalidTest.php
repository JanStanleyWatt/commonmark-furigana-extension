<?php

/**
 * Copyright 2023 Jan stanray watt

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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \JSW\Furigana\Parser\FuriganaOpenParser
 *
 * @group invalid
 */
final class FuriganaInvalidTest extends TestCase
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

    /**
     * @covers ::parse
     */
    public function testEmptyParentText(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張機能｜《きのう》は素晴らしい');
        $actual = $converter->convert('この拡張機能｜《きのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers ::parse
     */
    public function testEmptyRubyTextDelimit(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rt></rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers  \JSW\Furigana\Delimiter\FuriganaDelimiterProcesser::process
     */
    public function testMissingOpenRubyTextDelimit(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect_1 = $this->makeExpect('｜この拡張機能》は素晴らしい');
        $actual_1 = $converter->convert('｜この拡張機能》は素晴らしい')->getContent();

        $expect_2 = $this->makeExpect('この拡張機能》は素晴らしい');
        $actual_2 = $converter->convert('この拡張機能》は素晴らしい')->getContent();

        $this->assertSame($expect_1, $actual_1, 'Existing open delimit is failed');
        $this->assertSame($expect_2, $actual_2, 'Not existing open delimit is failed');
    }

    /**
     * @covers  \JSW\Furigana\Parser\RubyTextParser::parse
     */
    public function testMissingCloseRubyTextDelimit(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect_1 = $this->makeExpect('｜この《拡張機能は素晴らしい');
        $actual_1 = $converter->convert('｜この《拡張機能は素晴らしい')->getContent();

        $expect_2 = $this->makeExpect('この拡張機能《は素晴らしい');
        $actual_2 = $converter->convert('この拡張機能《は素晴らしい')->getContent();

        $this->assertSame($expect_1, $actual_1, 'Existing open delimit is failed');
        $this->assertSame($expect_2, $actual_2, 'Not existing open delimit is failed');
    }
}
