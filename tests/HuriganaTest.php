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
 * @coversDefaultClass \JSW\Hurigana\Delimiter\HuriganaDelimiterProcesser
 *
 * @group hurigana
 */
final class HuriganaTest extends TestCase
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
     * @covers ::useRPTag
     */
    public function testRubyParentheses(): void
    {
        $environment = new Environment([
            'hurigana' => [
                'use_rp_tag' => true,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension())
                    ->addExtension(new HuriganaExtension());

        $converter = new MarkdownConverter($environment);

        $expect = $this->makeExpect('この<ruby>拡張機能<rp>(</rp><rt>かくちょうきのう</rt><rp>)</rp></ruby>は素晴らしい');
        $actual = $converter->convert('この拡張機能《かくちょうきのう》は素晴らしい')->getContent();

        $this->assertSame($expect, $actual);
    }

}