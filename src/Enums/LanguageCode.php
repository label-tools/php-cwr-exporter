<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum LanguageCode: string
{
    case ABKHAZIAN = 'AB';
    case AFAR = 'AA';
    case AFRIKAANS = 'AF';
    case ALBANIAN = 'SQ';
    case AMHARIC = 'AM';
    case ARABIC = 'AR';
    case ASSAMESE = 'AS';
    case AYMARA = 'AY';
    case AZERBAIJANI = 'AZ';

    case BASHKIR = 'BA';
    case BASQUE = 'EU';
    case BHUTANI = 'DZ';
    case BIHARI = 'BH';
    case BISLAMA = 'BI';
    case BENGALI = 'BN';
    case BRETON = 'BR';
    case BULGARIAN = 'BG';
    case BURMESE = 'MY';
    case BYELORUSSIAN = 'BE';

    case CAMBODIAN = 'KM';
    case CATALAN = 'CA';
    case CHINESE = 'ZH';
    case CORSICAN = 'CO';
    case CROATIAN = 'HR';
    case CZECH = 'CS';

    case DANISH = 'DA';
    case DUTCH = 'NL';

    case ENGLISH = 'EN';
    case ESPERANTO = 'EO';
    case ESTONIAN = 'ET';

    case FAEROESE = 'FO';
    case FARSI = 'FA';
    case FIJIAN = 'FJ';
    case FINNISH = 'FI';
    case FRENCH = 'FR';
    case FRISIAN = 'FY';

    case GALICIAN = 'GL';
    case GEORGIAN = 'KA';
    case GERMAN = 'DE';
    case GREEK = 'EL';
    case GREENLANDIC = 'KL';
    case GUARANI = 'GN';
    case GUJARATI = 'GU';

    case HAUSA = 'HA';
    case HAWAIIAN = 'HW';
    case HEBREW = 'IW';
    case HINDI = 'HI';
    case HUNGARIAN = 'HU';

    case ICELANDIC = 'IS';
    case INDONESIAN = 'IN';
    case INTERLINGUA = 'IA';
    case INTERLINGUE = 'IE';
    case INUPIAK = 'IK';
    case IRISH = 'GA';
    case ITALIAN = 'IT';

    case JAPANESE = 'JA';
    case JAVANESE = 'JW';

    case KANNADA = 'KN';
    case KASHMIRI = 'KS';
    case KAZAKH = 'KK';
    case KINYARWANDA = 'RW';
    case KIRGHIZ = 'KY';
    case KIRUNDI = 'RN';
    case KOREAN = 'KO';
    case KURDISH = 'KU';

    case LAOTHIAN = 'LO';
    case LATIN = 'LA';
    case LINGALA = 'LN';
    case LITHUANIAN = 'LT';
    case LATVIAN = 'LV';

    case MACEDONIAN = 'MK';
    case MALAGASY = 'MG';
    case MALAY = 'MS';
    case MALAYALAM = 'ML';
    case MALTESE = 'MT';
    case MOLDAVIAN = 'MO';
    case MONGOLIAN = 'MN';

    case NAURU = 'NA';
    case NDEBELE = 'ND';
    case NEPALI = 'NE';
    case NORTHERN_SOTHO = 'NS';
    case NORWEGIAN = 'NO';

    case OCCITAN = 'OC';
    case ORIYA = 'OR';
    case OROMO = 'OM';

    case PAPIAMENTO = 'PM';
    case PASHTO = 'PS';
    case POLISH = 'PL';
    case PORTUGUESE = 'PT';
    case PUNJABI = 'PA';

    case QUECHUA = 'QU';

    case RHAETO_ROMANCE = 'RM';
    case ROMANIAN = 'RO';
    case RUSSIAN = 'RU';

    case SAMOAN = 'SM';
    case SANGRO = 'SG';
    case SANSKRIT = 'SA';
    case SCOTS_GAELIC = 'GD';
    case SERBIAN = 'SR';
    case SERBO_CROATIAN = 'SH';
    case SESOTHO = 'ST';
    case SETSWANA = 'TN';
    case SHONA = 'SN';
    case SINDHI = 'SD';
    case SINGHALESE = 'SI';
    case SISWATI = 'SS';
    case SLOVAK = 'SK';
    case SLOVENIAN = 'SL';
    case SPANISH = 'ES';
    case SOMALI = 'SO';
    case SUDANESE = 'SU';
    case SWAHILI = 'SW';
    case SWEDISH = 'SV';

    case TAGALOG = 'TL';
    case TAJIK = 'TG';
    case TAMIL = 'TA';
    case TATAR = 'TT';
    case TELUGU = 'TE';
    case THAI = 'TH';
    case TIBETAN = 'BO';
    case TIGRINYA = 'TI';
    case TONGA = 'TO';
    case TSONGA = 'TS';
    case TURKISH = 'TR';
    case TURKMEN = 'TK';
    case TWI = 'TW';

    case UKRAINIAN = 'UK';
    case URDU = 'UR';
    case UZBEK = 'UZ';

    case VENDA = 'VE';
    case VIETNAMESE = 'VI';
    case VOLAPUK = 'VO';

    case WOLOF = 'WO';
    case WELSH = 'CY';

    case XHOSA = 'XH';

    case YIDDISH = 'JI';
    case YORUBA = 'YO';

    case ZULU = 'ZU';

    public function getName(): string
    {
        return match ($this) {
            self::OROMO => '(Afan) Oromo',
            // Add other overrides here as needed
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

}