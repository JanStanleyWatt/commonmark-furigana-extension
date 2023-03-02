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
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \JSW\Furigana\Delimiter\FuriganaDelimiterProcesser
 *
 * @group furigana
 */
final class FuriganaTest extends TestCase
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

    /**
     * @covers \JSW\Furigana\Util\FuriganaKugiri::getKugiri
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::parse
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::getMatchDefinition
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::__construct
     */
    public function testRubySeparateDelimited(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張機能は<ruby>素晴らしい<rt>Wonderful</rt></ruby>');
        $actual = $converter->convert('この拡張機能は｜素晴らしい《Wonderful》')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers \JSW\Furigana\Util\FuriganaKugiri::getKugiri
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::parse
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::getMatchDefinition
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::__construct
     */
    public function testRubySeparateKanji(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rt>かくちょうきのう</rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers \JSW\Furigana\Util\FuriganaKugiri::getKugiri
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::parse
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::getMatchDefinition
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::__construct
     */
    public function testRubySeparateZenkakuAlphabet(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張機能は<ruby>Ｅｘｃｅｌｌｅｎｔ<rt>すばらしい</rt></ruby>');
        $actual = $converter->convert('この拡張機能はＥｘｃｅｌｌｅｎｔ《すばらしい》')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers \JSW\Furigana\Util\FuriganaKugiri::getKugiri
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::parse
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::getMatchDefinition
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::__construct
     */
    public function testRubySeparateHankakuAlphabet(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張機能は<ruby>Excellent<rt>すばらしい</rt></ruby>');
        $actual = $converter->convert('この拡張機能はExcellent《すばらしい》')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers \JSW\Furigana\Util\FuriganaKugiri::getKugiri
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::parse
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::getMatchDefinition
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::__construct
     */
    public function testRubySeparateZenkakuKatakana(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張機能は<ruby>グレート<rt>Great</rt></ruby>だ');
        $actual = $converter->convert('この拡張機能はグレート《Great》だ')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers \JSW\Furigana\Util\FuriganaKugiri::getKugiri
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::parse
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::getMatchDefinition
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::__construct
     */
    public function testRubySeparateHankakuKatakana(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張機能は<ruby>ｸﾞﾚｰﾄ<rt>Great</rt></ruby>だ');
        $actual = $converter->convert('この拡張機能はｸﾞﾚｰﾄ《Great》だ')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers \JSW\Furigana\Util\FuriganaKugiri::getKugiri
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::parse
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::getMatchDefinition
     * @covers \JSW\Furigana\Parser\FuriganaOpenParser::__construct
     */
    public function testRubySeparateHiragana(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張機能、<ruby>いいかんじ<rt>some good</rt></ruby>だ');
        $actual = $converter->convert('この拡張機能、いいかんじ《some good》だ')->getContent();

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers ::process
     */
    public function testRubyParentheses(): void
    {
        $environment = new Environment([
            'furigana' => [
                'use_rp_tag' => true,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rp>(</rp><rt>かくちょうきのう</rt><rp>)</rp></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }
}
