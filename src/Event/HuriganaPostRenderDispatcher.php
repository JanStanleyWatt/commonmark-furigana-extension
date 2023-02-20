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

namespace JSW\Hurigana\Event;

use League\CommonMark\Event\DocumentRenderedEvent;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Output\RenderedContent;

final class HuriganaPostRenderDispatcher
{
    private function makeTag(string $text, array $tagnames): string
    {
        if (empty($tagnames)) {
            return $text;
        }

        $answer = '';

        foreach ($tagnames as $name) {
            $answer .= '<'.$name.'>';
        }
        $answer .= $text;
        $tagnames = array_reverse($tagnames);
        foreach ($tagnames as $name) {
            $answer .= '</'.$name.'>';
        }

        return $answer;
    }

    private function divideParentTag(string $parent)
    {
        $stack = [];
        $answer = [];

        // 中にタグが無ければ単に文字列を分割する
        if (null === preg_match('/\<(.*?)\>.*\<(\1)\>/u', $parent)) {
            return mb_str_split($parent);
        }

        while ('' !== $parent) {
            // 先頭にタグがある場合は、タグ名を切り出す
            if (preg_match('/(?<=^\<)([^\/]+?)(?=\>)/u', $parent, $matches_1)) {
                $stack[] = $matches_1[0];
                // タグを取り除く
                $parent = preg_replace('/^\<'.$matches_1[0].'\>/u', '', $parent);

            // 先頭に閉じタグがある場合は、タグを取り除く
            } elseif (preg_match('/(?<=^\<\/)(.+?)(?=\>)/u', $parent, $matches_2)) {
                array_pop($stack);
                $parent = preg_replace('/^\<\/'.$matches_2[0].'\>/u', '', $parent);
            }
            // タグが無い場合
            else {
                // 一文字ずつ切り出して新しいタグにしていく
                preg_match('/^[^<]+?/u', $parent, $matches_3);
                $answer[] = $this->makeTag($matches_3[0], $stack);

                // テキストを一文字ずつ取り除く
                $parent = preg_replace('/^[^<]+?/u', '', $parent);
            }
        }

        return $answer;
    }

    private function makeMonoRuby(string $html)
    {
        // [0] => 親文字、[1] => ルビ
        $divides = explode('<rt>', $html);

        // 半角スペースで区切られたルビの数を数える
        preg_match('/([^<]+)/u', $divides[1], $ruby_match);
        $ruby = $ruby_match[0] ?? '';
        $ruby_count = count(explode(' ', $ruby));
        // 頭文字の数はタグを排除してから数える
        $pure_parent = preg_replace('/<.*?>/u', '', $divides[0]);
        $parent = $divides[0] ?? '';
        $parent_count = mb_strlen($pure_parent);

        // 頭文字の文字数と半角スペースで区切られたルビの数が一致していればモノルビにする
        if ($ruby_count === $parent_count) {
            $monoruby = '';

            $rubies = preg_replace('/.+/u', '<rt>$0</rt>', explode(' ', $ruby));
            $parents = $this->divideParentTag($parent);

            foreach ($rubies as $key => $ruby) {
                $monoruby .= $parents[$key];
                $monoruby .= $rubies[$key];
            }

            return $monoruby;
        } else {
            return $html;
        }
    }

    public function PostRender(DocumentRenderedEvent $event)
    {
        $html = $event->getOutput()->getContent();

        preg_match_all('/(?<=\<ruby\>)(.+?)(?=\<\/ruby\>)/u', $html, $rubies);

        $repraced = [];
        foreach ($rubies[0] as $ruby) {
            $tmp = $this->makeMonoRuby($ruby);

            $repraced[] = $tmp;
        }
        $html = str_replace($rubies[0], $repraced, $html);

        $event->replaceOutput(new RenderedContent(new Document(), $html));
    }
}
