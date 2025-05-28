<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum CwrWorkType: string
{
    case AAA_TRIPLE_A = 'TA';
    case ADULT_CONTEMPORARY = 'AC';
    case ALBUM_ORIENTED_ROCK_AOR = 'AR';
    case ALTERNATIVE_MUSIC = 'AL';
    case AMERICANA = 'AM';
    case BAND = 'BD';
    case BLUEGRASS_MUSIC = 'BL';
    case CHILDRENS_MUSIC = 'CD';
    case CLASSICAL_MUSIC = 'CL';
    case CONTEMPORARY_CHRISTIAN = 'CC';
    case COUNTRY_MUSIC = 'CT';
    case DANCE = 'DN';
    case FILM_TELEVISION_MUSIC = 'FM';
    case FOLK_MUSIC = 'FK';
    case GOSPEL_BLACK = 'BG';
    case GOSPEL_SOUTHERN = 'SG';
    case JAZZ_MUSIC = 'JZ';
    case JINGLES = 'JG';
    case LATIN = 'LN';
    case LATINA = 'LA';
    case NEW_AGE = 'NA';
    case OPERA = 'OP';
    case POLKA_MUSIC = 'PK';
    case POP_MUSIC = 'PP';
    case RAP_MUSIC = 'RP';
    case ROCK_MUSIC = 'RK';
    case RHYTHM_AND_BLUES = 'RB';
    case SACRED = 'SD';
    case SYMPHONIC = 'SY';

    public function getName(): string
    {
        return match ($this) {
            self::AAA_TRIPLE_A => 'AAA (Triple A)',
            self::ALBUM_ORIENTED_ROCK_AOR => 'Album Oriented Rock (AOR)',
            self::GOSPEL_BLACK => 'Gospel (Black)',
            self::GOSPEL_SOUTHERN => 'Gospel (Southern)',
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

}