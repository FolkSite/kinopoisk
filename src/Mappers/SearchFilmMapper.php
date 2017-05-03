<?php

namespace Siqwell\Kinopoisk\Mappers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Siqwell\Kinopoisk\Models\Film;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class SearchFilmMapper
 * @package Siqwell\Kinopoisk\Mappers
 */
class SearchFilmMapper extends Mapper
{

    /**
     * @var Collection
     */
    protected $result;

    /**
     * @return mixed
     */
    public function get()
    {
        $this->result = collect();

        $this->crawler->filter('.search_results .element')->each(function (Crawler $node, $index) {
            $this->result->push(
                new Film([
                    'id'       => $node->filter('.pic a')->attr('data-id'),
                    'type'     => $node->filter('.pic a')->attr('data-type'),
                    'title'    => $node->filter('.pic a img')->attr('alt'),
                    'original' => $this->original($node),
                    'year'     => $node->filter('.info .name .year')->text(),
                    'poster'   => $this->poster($node->filter('.pic a img')),
                ])
            );
        });

        return $this->result;
    }

    /**
     * @param Crawler $node
     * @return string|null
     */
    private function poster(Crawler $node)
    {
        if (Str::contains($node->attr('title'), 'no-poster.gif')) {
            return null;
        }

        if ($path = $node->attr('title')) {
            $path = Str::replaceFirst('/sm_film/', '/film_big/', $path);

            return (string)$node->getBaseHref()->withPath($path);
        }

        return null;
    }

    /**
     * @param Crawler $node
     * @return null
     */
    private function original(Crawler $node)
    {
        if ($text = $node->filter('.gray')->first()->text()) {
            $text = collect(explode(',', $text))->first();

            if (Str::contains($text, 'мин')) {
                return null;
            }

            return $text;
        }

        return null;
    }
}