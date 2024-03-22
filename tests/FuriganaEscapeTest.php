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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass('\JSW\Hurigana\Parser\FuriganaEscapeParser')]
#[CoversFunction('parse')]
#[Group('unit')]
#[Group('jisage')]
final class FuriganaEscapeTest extends TestCase
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


    #[Test]
    public function testEscapeOpenDelimiter(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('｜この拡張<ruby>機能<rt>きのう</rt></ruby>は素晴らしい');
        $actual = $converter->convert('\｜この拡張｜機能《きのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }


    #[Test]
    public function testEscapeRubyTextDelimiter(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この拡張《<ruby>機能<rt>きのう</rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張\《機能《きのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

    #[Test]
    public function testEscapeCloseDelimiter(): void
    {
        $environment = new Environment($this::DEFAULT_RULE);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new FuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rt>き》のう</rt></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《き\》のう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }
}
